<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Operation\OperationFromRouteFactory;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Route\RouteScanner;
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
     * @var string
     */
    private const ASSETS = __DIR__ . DS . '..' . DS . '..' . DS . 'assets' . DS;

    private array $array = [];

    private ModelScanner $modelScanner;

    private RouteScanner $routeScanner;

    private Configuration $config;

    /**
     * @param \SwaggerBake\Lib\Model\ModelScanner $modelScanner ModelScanner instance
     * @throws \ReflectionException
     */
    public function __construct(ModelScanner $modelScanner)
    {
        $this->modelScanner = $modelScanner;
        $this->routeScanner = $modelScanner->getRouteScanner();
        $this->config = $modelScanner->getConfig();

        $this->array = Yaml::parseFile($this->config->getYml());

        $this->buildSchemaFromYml();
        $this->buildPathsFromYml();

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.initialize', $this)
        );

        $xSwaggerBake = Yaml::parseFile(self::ASSETS . 'x-swagger-bake.yaml');

        $this->array['x-swagger-bake'] = array_merge_recursive(
            $xSwaggerBake['x-swagger-bake'],
            $this->array['x-swagger-bake'] ?? []
        );

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
     * @return false|string
     */
    public function toString()
    {
        EventManager::instance()->dispatch(
            new Event('SwaggerBake.beforeRender', $this)
        );

        return json_encode($this->getArray(), $this->config->get('jsonOptions'));
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
            $schema->setRefPath('#/components/schemas/' . $name);
            $this->array['components']['schemas'][$name] = $schema;
        }

        return $this;
    }

    /**
     * Adds a Schema element to OpenAPI 3.0 spec
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return $this
     */
    public function pushVendorSchema(Schema $schema)
    {
        $name = $schema->getName();
        if (!isset($this->array['x-swagger-bake']['components']['schemas'][$name])) {
            $schema->setRefPath('#/x-swagger-bake/components/schemas/' . $name);
            $this->array['x-swagger-bake']['components']['schemas'][$name] = $schema;
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

        return $this->array['x-swagger-bake']['components']['schemas'][$name] ?? null;
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
     * @throws \ReflectionException
     */
    private function buildSchemasFromModels(): void
    {
        $schemaFactory = new SchemaFactory();
        $models = $this->modelScanner->getModelDecorators();

        foreach ($models as $model) {
            $entityName = (new \ReflectionClass($model->getModel()->getEntity()))->getShortName();

            if ($this->getSchemaByName($entityName)) {
                continue;
            }

            $schema = $schemaFactory->create($model);
            if (!$schema) {
                continue;
            }

            if ($schema->isPublic()) {
                $this->pushSchema($schema);
            } else {
                $this->pushVendorSchema($schema);
            }

            $readSchema = $schemaFactory->create($model, $schemaFactory::READABLE_PROPERTIES);
            $this->pushVendorSchema(
                $readSchema->setName($readSchema->getReadSchemaName())
            );

            $writeSchema = $schemaFactory->create($model, $schemaFactory::WRITEABLE_PROPERTIES);
            $this->pushVendorSchema(
                $writeSchema->setName($writeSchema->getWriteSchemaName())
            );

            $propertiesRequiredOnCreate = array_filter($writeSchema->getProperties(), function ($property) {
                return $property->isRequirePresenceOnCreate() || $property->isRequired();
            });

            $addSchema = clone $writeSchema;
            $this->pushVendorSchema(
                $addSchema
                    ->setName($schema->getAddSchemaName())
                    ->setProperties([])
                    ->setAllOf([
                        ['$ref' => $this->getSchemaByName($schema->getWriteSchemaName())->getRefPath()],
                    ])
                    ->setRequired(array_keys($propertiesRequiredOnCreate))
            );

            $propertiesRequiredOnUpdate = array_filter($writeSchema->getProperties(), function ($property) {
                return $property->isRequirePresenceOnUpdate() || $property->isRequired();
            });

            $editSchema = clone $writeSchema;
            $this->pushVendorSchema(
                $editSchema
                    ->setName($schema->getEditSchemaName())
                    ->setProperties([])
                    ->setAllOf([
                        ['$ref' => $this->getSchemaByName($schema->getWriteSchemaName())->getRefPath()],
                    ])
                    ->setRequired(array_keys($propertiesRequiredOnUpdate))
            );
        }
    }

    /**
     * Builds paths from cake routes
     *
     * @return void
     */
    private function buildPathsFromRoutes(): void
    {
        $routes = $this->routeScanner->getRoutes();
        $operationFactory = new OperationFromRouteFactory($this);

        $ignorePaths = array_keys($this->array['paths']);

        foreach ($routes as $route) {
            $resource = $route->templateToOpenApiPath();

            if ($this->hasPathByResource($resource)) {
                $path = $this->array['paths'][$resource];
            } else {
                $path = (new PathFromRouteFactory($route))->create();
            }

            if (!$path instanceof Path || in_array($path->getResource(), $ignorePaths)) {
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
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private function getSchemaFromRoute(RouteDecorator $route): ?Schema
    {
        $controller = $route->getController();
        $name = preg_replace('/\s+/', '', $controller);

        if (in_array(strtolower($route->getAction()), ['add','view','edit','index','delete'])) {
            return $this->getSchemaByName(Inflector::singularize($name));
        }

        return null;
    }

    /**
     * Build paths from YML
     *
     * @todo for now an array will work, but should apply proper Path objects in the future
     * @return void
     */
    private function buildPathsFromYml(): void
    {
        if (!isset($this->array['paths'])) {
            $this->array['paths'] = [];
        }
    }

    /**
     * Build schema from YML
     *
     * @return void
     */
    private function buildSchemaFromYml(): void
    {
        if (!isset($this->array['components']['schemas'])) {
            $this->array['components']['schemas'] = [];
        }

        $factory = new SchemaFromYamlFactory();

        foreach ($this->array['components']['schemas'] as $schemaName => $schemaVar) {
            $schema = $factory->create($schemaName, $schemaVar)->setRefPath('#/components/schemas/' . $schemaName);
            $this->array['components']['schemas'][$schemaName] = $schema;
        }
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
