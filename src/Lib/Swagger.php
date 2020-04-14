<?php


namespace SwaggerBake\Lib;

use Cake\Routing\Route\Route;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Factory as Factory;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
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

    public function toString()
    {
        return json_encode($this->getArray(), JSON_PRETTY_PRINT);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function writeFile(string $output) : void
    {
        if (!is_writable($output)) {
            throw new SwaggerBakeRunTimeException("Output file is not writable, given $output");
        }

        file_put_contents($output, $this->toString());
    }

    public function pushSchema(Schema $schema): Swagger
    {
        $name = $schema->getName();
        if (!isset($this->array['components']['schemas'][$name])) {
            $this->array['components']['schemas'][$name] = $schema;
        }
        return $this;
    }

    public function getSchemaByName(string $name): ?Schema
    {
        if (isset($this->array['components']['schemas'][$name])) {
            return $this->array['components']['schemas'][$name];
        }

        return null;
    }

    public function pushPath(Path $path): Swagger
    {
        $route = $path->getPath();
        $methodType = $path->getType();
        if (!$this->getPathByRouteAndMethodType($route, $methodType)) {
            $this->array['paths'][$route][$methodType] = $path;
        }
        return $this;
    }

    public function getPathByRouteAndMethodType(string $route, string $methodType): ?Path
    {
        if (isset($this->array['paths'][$route][$methodType])) {
            return $this->array['paths'][$route][$methodType];
        }

        return null;
    }

    public function getConfig() : Configuration
    {
        return $this->config;
    }

    private function buildSchemas(): void
    {
        $schemaFactory = new Factory\SchemaFactory();
        $models = $this->cakeModel->getModels();

        foreach ($models as $model) {
            if ($this->getSchemaByName($model->getName())) {
                continue;
            }
            $schema = $schemaFactory->create($model);
            $this->pushSchema($schema);
        }
    }

    private function buildPaths(): void
    {
        $prefix = $this->cakeModel->getPrefix();

        foreach ($this->cakeRoute->getRoutes() as $route) {

            $path = (new Factory\PathFactory($route, $this->config))->create();
            if (is_null($path)) {
                continue;
            }

            if ($this->getPathByRouteAndMethodType($path->getPath(), $path->getType())) {
                continue;
            }

            $path->setResponses($this->getPathResponses($path));
            $path = $this->withPathParameters($path, $route);
            $path = $this->withRequestBody($path, $route);

            $this->pushPath($path);
        }
    }

    private function withPathParameters(Path $path, Route $route) : Path
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

    private function getPathResponses(Path $path) : array
    {
        $return = [];
        foreach ($path->getTags() as $tag) {
            $className = Inflector::classify($tag);
            $response = new Response();

            if ($this->getSchemaByName($className)) {
                $response->setSchemaRef('#/components/schemas/' . $className);
            }

            $return[200] = $response->setCode(200);
        }

        return $return;
    }

    private function withRequestBody(Path $path, Route $route) : Path
    {
        $requestBody = (new RequestBodyBuilder($path, $this, $route))->build();
        if ($requestBody) {
            $path->setRequestBody($requestBody);
        }
        return $path;
    }
}