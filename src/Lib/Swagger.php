<?php


namespace SwaggerBake\Lib;

use Cake\Utility\Inflector;
use LogicException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Factory as Factory;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Parameter;
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
        if (!isset($array['components'])) {
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

            $requestBody = $this->getRequestBody($path);
            if ($requestBody) {
                $path->setRequestBody($requestBody);
            }

            $headers = (new HeaderParameter($route, $this->config))->getHeaderParameters();
            foreach ($headers as $parameter) {
                $path->pushParameter($parameter);
            }

            $queries = (new QueryParameter($route, $this->config))->getQueryParameters();
            foreach ($queries as $parameter) {
                $path->pushParameter($parameter);
            }

            $this->pushPath($path);
        }
    }

    private function getPathResponses(Path $path) : array
    {
        $return = [];
        foreach ($path->getTags() as $tag) {
            $className = Inflector::classify($tag);
            if (!$this->getSchemaByName($className)) {
                continue;
            }

            $schemaRef = '#/components/schemas/' . $className;

            $response = new Response();
            $response
                ->setCode(200)
                ->setSchemaRef($schemaRef)
            ;

            $return[200] = $response;
        }

        return $return;
    }

    private function getRequestBody(Path $path) : ?RequestBody
    {
        foreach ($path->getTags() as $tag) {
            $className = Inflector::classify($tag);
            if (!$this->getSchemaByName($className)) {
                continue;
            }

            if (!in_array($path->getType(), ['put','patch', 'post'])) {
                continue;
            }

            $schema = new Schema();
            $schema->setType('object');

            foreach ($this->getSchemaByName($className)->getProperties() as $propertyName => $property) {

                if (isset($property['readOnly']) && $property['readOnly'] == 1) {
                    continue;
                }

                $schemaProperty = new SchemaProperty();
                $schemaProperty
                    ->setName($propertyName)
                    ->setType($property['type']);
                ;

                $schema->pushProperty($schemaProperty);
            }


            $content = new Content();
            $content
                ->setMimeType('application/x-www-form-urlencoded')
                ->setSchema($schema);
            ;

            $requestBody = new RequestBody();
            $requestBody
                ->pushContent($content)
                ->setRequired(true)
            ;

            return $requestBody;
        }

        return null;
    }

    private function getRequestBodySchema(Path $path) : array
    {
        $return = [];

        foreach ($path->getTags() as $tag) {
            $className = Inflector::classify($tag);
            $schema = $this->getSchemaByName($className);

            if (!$schema) {
                continue;
            }

            if (in_array($path->getType(), ['put','patch', 'post'])) {

                $parameter = new Parameter();
                $parameter->setIn('body');

                $swaggerSchema = new Schema();
                $schemaProperties = [];

                foreach ($schema->getProperties() as $propertyName => $property) {

                    if (isset($property['readOnly']) && $property['readOnly'] == 1) {
                        continue;
                    }

                    $schemaProperty = new SchemaProperty();
                    $schemaProperty
                        ->setName($propertyName)
                        ->setType($property['type']);
                    ;

                    $schemaProperties[$propertyName] = $schemaProperty;
                }

                $swaggerSchema->setProperties($schemaProperties);
                $return[] = $parameter->setSchema($swaggerSchema);
            }
        }

        return $return;
    }
}