<?php

namespace SwaggerBake\Lib;

use Cake\Utility\Inflector;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Factory as Factory;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use Symfony\Component\Yaml\Yaml;

class Swagger
{
    private $array = [];
    private $cakeModel;
    private $cakeRoute;
    private $config;

    public function __construct(CakeModel $cakeModel)
    {
        $this->cakeModel = $cakeModel;
        $this->cakeRoute = $cakeModel->getCakeRoute();
        $this->config = $cakeModel->getConfig();

        $array = Yaml::parseFile($this->config->getYml());
        if (!isset($array['paths'])) {
            $array['paths'] = [];
        }
        if (!isset($array['components']['schemas'])) {
            $array['components']['schemas'] = [];
        }

        $this->array = $array;
    }

    /**
     * Returns OpenAPI 3.0 specification as an array
     *
     * @return array
     */
    public function getArray(): array
    {
        $this->buildSchemas();
        $this->buildPaths();

        foreach ($this->array['paths'] as $method => $paths) {
            foreach ($paths as $pathId => $path) {
                if (!is_array($path)) {
                    $this->array['paths'][$method][$pathId] = $path->toArray();
                }
            }
        }

        foreach ($this->array['components']['schemas'] as $schema) {
            if (!is_array($schema)) {
                $schema->toArray();
            }
        }

        ksort($this->array['paths']);
        ksort($this->array['components']['schemas']);

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
    public function writeFile(string $output) : void
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
        $route = $path->getPath();
        $methodType = $path->getType();
        if (!$this->getPathByRouteAndMethodType($route, $methodType)) {
            $this->array['paths'][$route][$methodType] = $path;
        }
        return $this;
    }

    /**
     * Returns a path by $route and $methodType argument
     *
     * @example $swagger->getPathByRouteAndMethodType('/my/route', 'post')
     *
     * @param string $route
     * @param string $methodType
     * @return Path|null
     */
    public function getPathByRouteAndMethodType(string $route, string $methodType): ?Path
    {
        if (isset($this->array['paths'][$route][$methodType])) {
            return $this->array['paths'][$route][$methodType];
        }

        return null;
    }

    /**
     * Return the configuration
     *
     * @return Configuration
     */
    public function getConfig() : Configuration
    {
        return $this->config;
    }

    private function buildSchemas(): void
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

    private function buildPaths(): void
    {
        $routes = $this->cakeRoute->getRoutes();
        foreach ($routes as $route) {

            $path = (new Factory\PathFactory($route, $this->config))->create();
            if (is_null($path)) {
                continue;
            }

            if ($this->getPathByRouteAndMethodType($path->getPath(), $path->getType())) {
                continue;
            }

            $path = $this->pathWithResponses($path);
            $path = $this->pathWithSecurity($path, $route);
            $path = $this->pathWithParameters($path, $route);
            $path = $this->pathWithRequestBody($path, $route);

            $this->pushPath($path);
        }
    }

    private function pathWithSecurity(Path $path, ExpressiveRoute $route) : Path
    {
        $path->setSecurity((new Security($route, $this->config))->getPathSecurity());
        return $path;
    }

    private function pathWithParameters(Path $path, ExpressiveRoute $route) : Path
    {
        $headers = (new HeaderParameter($route, $this->config))->getHeaderParameters();
        foreach ($headers as $parameter) {
            $path->pushParameter($parameter);
        }

        $queries = (new QueryParameter($route, $this->config))->getQueryParameters();
        foreach ($queries as $parameter) {
            $path->pushParameter($parameter);
        }
        return $path;
    }

    private function pathWithResponses(Path $path) : Path
    {
        foreach ($path->getTags() as $tag) {
            $className = Inflector::classify($tag);

            if (!$path->hasSuccessResponseCode() && $this->getSchemaByName($className)) {
                $response = new Response();
                $response
                    ->setSchemaRef('#/components/schemas/' . $className)
                    ->setCode(200);
                $path->pushResponse($response);
            }
        }

        if (!$path->hasSuccessResponseCode()) {
            $path->pushResponse((new Response())->setCode(200));
        }

        return $path;
    }

    private function pathWithRequestBody(Path $path, ExpressiveRoute $route) : Path
    {
        $requestBody = (new RequestBodyBuilder($path, $this, $route))->build();
        if ($requestBody) {
            $path->setRequestBody($requestBody);
        }
        return $path;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}