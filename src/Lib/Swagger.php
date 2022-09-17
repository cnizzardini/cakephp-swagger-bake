<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Event\Event;
use Cake\Event\EventManager;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Utility\FileUtility;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Swagger
 *
 * @package SwaggerBake\Lib
 */
class Swagger
{
    /**
     * @var string
     */
    private const ASSETS = __DIR__ . DS . '..' . DS . '..' . DS . 'assets' . DS;

    private array $array = [];

    /**
     * @param \SwaggerBake\Lib\Model\ModelScanner $modelScanner ModelScanner instance
     * @param \SwaggerBake\Lib\Configuration $config Configuration instance
     * @param \SwaggerBake\Lib\Utility\FileUtility|null $fileUtility FileUtility will be created automatically if
     *  argument is null.
     * @throws \ReflectionException
     */
    public function __construct(
        private ModelScanner $modelScanner,
        private Configuration $config,
        private ?FileUtility $fileUtility = null
    ) {
        $this->fileUtility = $fileUtility ?? new FileUtility();
    }

    /**
     * Builds an OpenAPI array that can be converted to JSON.
     *
     * @return \SwaggerBake\Lib\Swagger
     * @throws \ReflectionException
     */
    public function build()
    {
        $this->array = (new OpenApiFromYaml())->build(Yaml::parseFile($this->config->getYml()));

        $xSwaggerBake = Yaml::parseFile(self::ASSETS . 'x-swagger-bake.yaml');

        $this->array['x-swagger-bake'] = array_merge_recursive(
            $xSwaggerBake['x-swagger-bake'],
            $this->array['x-swagger-bake'] ?? []
        );

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.initialize', $this)
        );

        $this->array = (new OpenApiSchemaGenerator($this->modelScanner))->generate($this->array);
        $this->array = (new OpenApiPathGenerator($this, $this->modelScanner->getRouteScanner(), $this->config))
            ->generate($this->array);

        return $this;
    }

    /**
     * Returns OpenAPI 3.0 specification as an array
     *
     * @return array
     */
    public function getArray(): array
    {
        foreach ($this->array['paths'] as $method => $paths) {
            foreach ($paths as $pathId => $path) {
                if ($path instanceof Path) {
                    $this->array['paths'][$method][$pathId] = $path->toArray();
                }
            }
        }

        foreach ($this->array['components']['schemas'] as $schema) {
            if (!is_array($schema)) {
                $schema->toArray();
            }
        }

        if (is_array($this->array['paths'])) {
            ksort($this->array['paths'], SORT_STRING);
        }

        if (is_array($this->array['components']['schemas'])) {
            uksort($this->array['components']['schemas'], function ($a, $b) {
                return strcasecmp(
                    preg_replace('/\s+/', '', $a),
                    preg_replace('/\s+/', '', $b)
                );
            });
        }

        if (empty($this->array['components']['schemas'])) {
            unset($this->array['components']['schemas']);
        }
        if (empty($this->array['components'])) {
            unset($this->array['components']);
        }

        return $this->array;
    }

    /**
     * @param array $array openapi array
     * @return $this
     */
    public function setArray(array $array)
    {
        $this->array = $array;

        return $this;
    }

    /**
     * Returns OpenAPI 3.0 spec as a JSON string
     *
     * @return string
     */
    public function toString(): string
    {
        $this->addAdditionalSchema();

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.beforeRender', $this)
        );

        $json = json_encode($this->getArray(), $this->config->getJsonOptions());
        if (!$json) {
            throw new SwaggerBakeRunTimeException('Error converting OpenAPI to JSON.');
        }

        return $json;
    }

    /**
     * Writes OpenAPI 3.0 spec to a file using the $output argument as a file path
     *
     * @param string $output Absolute file path
     * @return void
     */
    public function writeFile(string $output): void
    {
        if (!$this->fileUtility->isWritable($output)) {
            throw new SwaggerBakeRunTimeException("Output file is not writable, given `$output`");
        }

        if ($this->fileUtility->putContents($output, $this->toString()) === false) {
            throw new SwaggerBakeRunTimeException("Error encountered while writing swagger file to `$output`");
        }
    }

    /**
     * Returns a schema object by $name argument
     *
     * @param string $name Name of schema
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     */
    public function getSchemaByName(string $name): ?Schema
    {
        if (isset($this->array['components']['schemas'][$name])) {
            return $this->array['components']['schemas'][$name];
        }

        return $this->array['x-swagger-bake']['components']['schemas'][$name] ?? null;
    }

    /**
     * Return the configuration
     *
     * @return \SwaggerBake\Lib\Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Returns an array of Operation objects that do not have a 200-299 HTTP status code
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation[]
     */
    public function getOperationsWithNoHttp20x(): array
    {
        $operations = [];

        foreach ($this->array['paths'] as $path) {
            if (!$path instanceof Path) {
                continue;
            }

            $operations = array_merge(
                $operations,
                array_filter($path->getOperations(), function ($operation) {
                    return !$operation->hasSuccessResponseCode();
                })
            );
        }

        return $operations;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Adds OpenApiResponse schema and OpenApiDtoRequestBody schema to #/components/schemas
     *
     * @return void
     */
    private function addAdditionalSchema(): void
    {
        $paths = $this->array['paths'];
        if (empty($paths)) {
            return;
        }

        foreach ($paths as $path) {
            if (!$path instanceof Path) {
                continue;
            }
            foreach ($path->getOperations() as $operation) {
                $requestBody = $operation->getRequestBody();
                if ($requestBody instanceof RequestBody) {
                    foreach ($requestBody->getContent() as $content) {
                        if ($content->getSchema() instanceof Schema) {
                            $content = $this->addCustomSchema($content);
                        }
                    }
                }
                foreach ($operation->getResponses() as $response) {
                    foreach ($response->getContent() as $content) {
                        $content = $this->addCustomSchema($content);
                    }
                }
            }
        }
    }

    /**
     * Check if the Schema was added as custom and is set with a public visibility. If so, add the schema to
     * #/component/schema and update the content with the openapi $ref path. Do nothing otherwise.
     *
     * @param \SwaggerBake\Lib\OpenApi\Content $content The Content of the Response or RequestBody
     * @return \SwaggerBake\Lib\OpenApi\Content
     */
    private function addCustomSchema(Content $content): Content
    {
        $schema = $content->getSchema();
        if (!$schema instanceof Schema || !$schema->isCustomSchema()) {
            return $content;
        }

        if (in_array($schema->getVisibility(), [OpenApiSchema::VISIBLE_ALWAYS, OpenApiSchema::VISIBLE_DEFAULT])) {
            $schema->setRefPath('#/components/schemas/' . $schema->getName());
            if ($schema->getType() == 'array') {
                $schema->setType('object');
                $content->setSchema(
                    (new Schema())
                        ->setType('array')
                        ->setItems([
                            '$ref' => $schema->getRefPath(),
                        ])
                );
            } else {
                $content->setSchema($schema->getRefPath());
            }

            if (!isset($this->array['components']['schemas'][$schema->getName()])) {
                $this->array['components']['schemas'][$schema->getName()] = $schema;
            }
        }

        return $content;
    }
}
