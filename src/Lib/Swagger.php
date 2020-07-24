<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Utility\Inflector;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Operation\OperationFromRouteFactory;
use SwaggerBake\Lib\Path\PathFromRouteFactory;
use SwaggerBake\Lib\Schema\SchemaFactory;
use SwaggerBake\Lib\Schema\SchemaFromYamlFactory;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Swagger
 *
 * @package SwaggerBake\Lib
 */
class Swagger
{
    /**
     * @var array
     */
    private $array = [];

    /**
     * @var \SwaggerBake\Lib\CakeModel
     */
    private $cakeModel;

    /**
     * @var \SwaggerBake\Lib\CakeRoute
     */
    private $cakeRoute;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @param \SwaggerBake\Lib\CakeModel $cakeModel CakeModel
     */
    public function __construct(CakeModel $cakeModel)
    {
        $this->cakeModel = $cakeModel;
        $this->cakeRoute = $cakeModel->getCakeRoute();
        $this->config = $cakeModel->getConfig();
        $this->buildFromYml();
        $this->buildSchemasFromModels();
        $this->buildPathsFromRoutes();
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

        ksort($this->array['paths'], SORT_STRING);
        uksort($this->array['components']['schemas'], function ($a, $b) {
            return strcasecmp(
                preg_replace('/\s+/', '', $a),
                preg_replace('/\s+/', '', $b)
            );
        });

        if (empty($this->array['components']['schemas'])) {
            unset($this->array['components']['schemas']);
        }
        if (empty($this->array['components'])) {
            unset($this->array['components']);
        }

        return $this->array;
    }

    /**
     * Returns OpenAPI 3.0 spec as a JSON string
     *
     * @return false|string
     */
    public function toString()
    {
        return json_encode($this->getArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Writes OpenAPI 3.0 spec to a file using the $output argument as a file path
     *
     * @param string $output Absolute file path
     * @return void
     */
    public function writeFile(string $output): void
    {
        if (!is_writable($output)) {
            throw new SwaggerBakeRunTimeException("Output file is not writable, given $output");
        }

        file_put_contents($output, $this->toString());

        if (!file_exists($output)) {
            throw new SwaggerBakeRunTimeException("Error encountered while writing swagger file to $output");
        }
    }

    /**
     * Adds a Schema element to OpenAPI 3.0 spec
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return $this
     */
    public function pushSchema(Schema $schema)
    {
        $name = $schema->getName();
        if (!isset($this->array['components']['schemas'][$name])) {
            $this->array['components']['schemas'][$name] = $schema;
        }

        return $this;
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

        return null;
    }

    /**
     * Adds a path to OpenAPI 3.0 spec
     *
     * @param \SwaggerBake\Lib\OpenApi\Path $path Path
     * @return $this
     */
    public function pushPath(Path $path)
    {
        $resource = $path->getResource();
        $this->array['paths'][$resource] = $path;

        return $this;
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
     * Builds schemas from cake models
     *
     * @return void
     */
    private function buildSchemasFromModels(): void
    {
        $schemaFactory = new SchemaFactory($this->config);
        $entities = $this->cakeModel->getEntityDecorators();

        foreach ($entities as $entity) {
            if ($this->getSchemaByName($entity->getName())) {
                continue;
            }
            $schema = $schemaFactory->create($entity);
            if (!$schema) {
                continue;
            }
            $this->pushSchema($schema);
        }
    }

    /**
     * Builds paths from cake routes
     *
     * @return void
     */
    private function buildPathsFromRoutes(): void
    {
        $routes = $this->cakeRoute->getRoutes();
        $operationFactory = new OperationFromRouteFactory($this);

        $ignorePaths = array_keys($this->array['paths']);

        foreach ($routes as $route) {
            $resource = $this->convertCakePathToOpenApiResource($route->getTemplate());
            if ($this->hasPathByResource($resource)) {
                $path = $this->array['paths'][$resource];
            } else {
                $path = (new PathFromRouteFactory($route, $this->config))->create();
            }

            if (!$path instanceof Path) {
                continue;
            }

            if (in_array($path->getResource(), $ignorePaths)) {
                continue;
            }

            foreach ($route->getMethods() as $httpMethod) {
                $schema = $this->getSchemaFromRoute($route);

                $operation = $operationFactory->create($route, $httpMethod, $schema);

                if (!$operation instanceof Operation) {
                    continue;
                }

                $path->pushOperation($operation);
            }

            if (!empty($path->getOperations())) {
                $this->pushPath($path);
            }
        }
    }

    /**
     * Gets the Schema associated with a Route
     *
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator $route RouteDecorator
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private function getSchemaFromRoute(RouteDecorator $route): ?Schema
    {
        $controller = $route->getController();
        $name = preg_replace('/\s+/', '', $controller);

        if (in_array(strtolower($route->getAction()), ['add','view','edit','index'])) {
            return $this->getSchemaByName(Inflector::singularize($name));
        }

        return null;
    }

    /**
     * Constructs the primary array used in this class from pre-defined swagger.yml
     *
     * @return void
     */
    private function buildFromYml(): void
    {
        $array = Yaml::parseFile($this->config->getYml());

        $array = $this->buildSchemaFromYml($array);
        $array = $this->buildPathsFromYml($array);

        $this->array = $array;
    }

    /**
     * Build paths from YML
     *
     * @todo for now an array will work, but should apply proper Path objects in the future
     * @param array $yaml OpenApi YAML as an array
     * @return array
     */
    private function buildPathsFromYml(array $yaml): array
    {
        if (!isset($yaml['paths'])) {
            $yaml['paths'] = [];
        }

        return $yaml;
    }

    /**
     * Build schema from YML
     *
     * @param array $yaml OpenApi YAML as an array
     * @return array
     */
    private function buildSchemaFromYml(array $yaml): array
    {
        if (!isset($yaml['components']['schemas'])) {
            $yaml['components']['schemas'] = [];
        }

        $factory = new SchemaFromYamlFactory();

        foreach ($yaml['components']['schemas'] as $schemaName => $schemaVar) {
            $yaml['components']['schemas'][$schemaName] = $factory->create($schemaName, $schemaVar);
        }

        return $yaml;
    }

    /**
     * Converts Cake path parameters to OpenApi Spec
     *
     * @example /actor/:id to /actor/{id}
     * @param string $resource Resource name
     * @return string
     */
    private function convertCakePathToOpenApiResource(string $resource): string
    {
        $pieces = array_map(
            function ($piece) {
                if (substr($piece, 0, 1) == ':') {
                    return '{' . str_replace(':', '', $piece) . '}';
                }

                return $piece;
            },
            explode('/', $resource)
        );

        if ($this->config->getPrefix() == '/') {
            return implode('/', $pieces);
        }

        return substr(
            implode('/', $pieces),
            strlen($this->config->getPrefix())
        );
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
     * @param string $resource Resource name
     * @return bool
     */
    private function hasPathByResource(string $resource): bool
    {
        return isset($this->array['paths'][$resource]);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
