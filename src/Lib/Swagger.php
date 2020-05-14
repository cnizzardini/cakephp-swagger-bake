<?php

namespace SwaggerBake\Lib;

use Cake\Utility\Inflector;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Factory as Factory;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Operation\OperationFromRouteFactory;
use SwaggerBake\Lib\Path\PathFromRouteFactory;
use Symfony\Component\Yaml\Yaml;

class Swagger
{
    /** @var array */
    private $array = [];

    /** @var CakeModel */
    private $cakeModel;

    /** @var CakeRoute */
    private $cakeRoute;

    /** @var Configuration */
    private $config;

    public function __construct(CakeModel $cakeModel)
    {
        $this->cakeModel = $cakeModel;
        $this->cakeRoute = $cakeModel->getCakeRoute();
        $this->config = $cakeModel->getConfig();
        $this->buildFromYml();
    }

    /**
     * Returns OpenAPI 3.0 specification as an array
     *
     * @return array
     */
    public function getArray(): array
    {
        $this->buildSchemasFromModels();
        $this->buildPathsFromRoutes();

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
     * @param string $output
     */
    public function writeFile(string $output): void
    {
        if (!is_writable($output)) {
            throw new SwaggerBakeRunTimeException("Output file is not writable, given $output");
        }

        file_put_contents($output, $this->toString());
    }

    /**
     * Adds a Schema element to OpenAPI 3.0 spec
     *
     * @param Schema $schema
     * @return Swagger
     */
    public function pushSchema(Schema $schema): Swagger
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
     * @param string $name
     * @return Schema|null
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
     * @param Path $path
     * @return $this
     */
    public function pushPath(Path $path): Swagger
    {
        $resource = $path->getResource();
        $this->array['paths'][$resource] = $path;
        return $this;
    }

    /**
     * Return the configuration
     *
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Builds schemas from cake models
     */
    private function buildSchemasFromModels(): void
    {
        $schemaFactory = new Factory\SchemaFactory($this->config);
        $models = $this->cakeModel->getModels();

        foreach ($models as $model) {
            if ($this->getSchemaByName($model->getName())) {
                continue;
            }
            $schema = $schemaFactory->create($model);
            if (!$schema) {
                continue;
            }
            $this->pushSchema($schema);
        }
    }

    /**
     * Builds paths from cake routes
     */
    private function buildPathsFromRoutes(): void
    {
        $routes = $this->cakeRoute->getRoutes();
        $operationFactory = new OperationFromRouteFactory($this->config);

        $ignorePaths = array_keys($this->array['paths']);

        foreach ($routes as $route) {

            $path = (new PathFromRouteFactory($route, $this->config))->create();

            if (empty($path)) {
                continue;
            }

            if (in_array($path->getResource(), $ignorePaths)) {
                continue;
            }

            foreach ($route->getMethods() as $httpMethod) {

                if (strtolower($httpMethod) == 'put') {
                    continue;
                }

                $schema = $this->getSchemaFromRoute($route);

                $operation = $operationFactory->create($route, $httpMethod, $schema);

                if (!$operation instanceof Operation) {
                    continue;
                }

                $this->addArrayOfObjectsSchema($operation);

                $path->pushOperation($operation);
            }

            if (!empty($path->getOperations())) {
                $this->pushPath($path);
            }
        }
    }

    /**
     * @param ExpressiveRoute $route
     * @return Schema|null
     */
    private function getSchemaFromRoute(ExpressiveRoute $route) : ?Schema
    {
        $controller = $route->getController();
        $name = preg_replace('/\s+/', '', $controller);

        if (in_array(strtolower($route->getAction()),['index']) && $this->getSchemaByName($name)) {
            return $this->getSchemaByName($name);
        }

        if (in_array(strtolower($route->getAction()),['add','view','edit'])) {
            return $this->getSchemaByName(Inflector::singularize($name));
        }

        return null;
    }

    /**
     * Adds array of objects to #/components/schemas
     *
     * @param Operation $operation
     */
    private function addArrayOfObjectsSchema(Operation $operation) : void
    {
        if (strtolower($operation->getHttpMethod()) == 'get' && strstr($operation->getOperationId(),':index')) {
            return;
        }
        $tags = $operation->getTags();
        $name = preg_replace('/\s+/', '', reset($tags));

        if ($this->getSchemaByName($name)) {
            return;
        }

        $this->pushSchema(
            (new Schema())
                ->setName($name)
                ->setType('array')
                ->setItems(['$ref' => '#/components/schemas/' . Inflector::classify($name)])
        );
    }

    /**
     * Constructs the primary array used in this class from pre-defined swagger.yml
     */
    private function buildFromYml() : void
    {
        $array = Yaml::parseFile($this->config->getYml());

        $array = $this->buildPathsFromYml($array);
        $array = $this->buildSchemaFromYml($array);

        $this->array = $array;
    }

    /**
     * Build paths from YML
     *
     * @todo for now an array will work, but should apply proper Path objects in the future
     * @param $array
     * @return array
     */
    private function buildPathsFromYml($array) : array
    {
        if (!isset($array['paths'])) {
            $array['paths'] = [];
        }

        return $array;
    }

    /**
     * Build schema from YML
     *
     * @param $array
     * @return array
     */
    private function buildSchemaFromYml($array) : array
    {
        if (!isset($array['components']['schemas'])) {
            $array['components']['schemas'] = [];
        }

        foreach ($array['components']['schemas'] as $schemaName => $schemaVar) {

            $schema = (new Schema())
                ->setName($schemaName)
                ->setType($schemaVar['type'])
                ->setDescription($schemaVar['description'] ?? '')
                ->setItems($schemaVar['items'] ?? []);

            $schemaVar['properties'] = $schemaVar['properties'] ?? [];

            foreach ($schemaVar['properties'] as $propertyName => $propertyVar) {
                $property = (new SchemaProperty())
                    ->setType($propertyVar['type'])
                    ->setName($propertyName)
                    ->setFormat($propertyVar['type'] ?? '')
                    ->setExample($propertyVar['example'] ?? '')
                ;
                $schema->pushProperty($property);
            }

            $array['components']['schemas'][$schemaName] = $schema;
        }

        return $array;
    }

    /**
     * Converts Cake path parameters to OpenApi Spec
     *
     * @example /actor/:id to /actor/{id}
     * @param string $resource
     * @return string
     */
    private function convertCakePathToOpenApiResource(string $resource) : string
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

    public function __toString(): string
    {
        return $this->toString();
    }
}