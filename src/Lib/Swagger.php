<?php

namespace SwaggerBake\Lib;

use Cake\Utility\Inflector;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Factory as Factory;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\PathSecurity;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
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
        $this->buildFromDefaults();
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
        if (!$this->hasPathByRouteAndMethodType($route, $methodType)) {
            $this->array['paths'][$route][$methodType] = $path;
        }
        return $this;
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

    /**
     * @param string $route
     * @param string $methodType
     * @return Path|null|mixed
     */
    private function hasPathByRouteAndMethodType(string $route, string $methodType): bool
    {
        return isset($this->array['paths'][$route][$methodType]);
    }

    /**
     * Builds schemas from cake models
     */
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

    /**
     * Builds paths from cake routes
     */
    private function buildPaths(): void
    {
        $routes = $this->cakeRoute->getRoutes();
        foreach ($routes as $route) {

            $path = (new Factory\PathFactory($route, $this->config))->create();
            if (is_null($path)) {
                continue;
            }

            if ($this->hasPathByRouteAndMethodType($path->getPath(), $path->getType())) {
                continue;
            }

            $path = $this->pathWithResponses($path);
            $path = $this->pathWithSecurity($path, $route);
            $path = $this->pathWithParameters($path, $route);
            $path = $this->pathWithRequestBody($path, $route);

            $this->pushPath($path);
        }
    }

    /**
     * Sets security on a path
     *
     * @param Path $path
     * @param ExpressiveRoute $route
     * @return Path
     */
    private function pathWithSecurity(Path $path, ExpressiveRoute $route) : Path
    {
        $path->setSecurity((new Security($route, $this->config))->getPathSecurity());
        return $path;
    }

    /**
     * Sets header parameters on a path
     *
     * @param Path $path
     * @param ExpressiveRoute $route
     * @return Path
     */
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

    /**
     * Sets responses on a path
     *
     * @param Path $path
     * @return Path
     */
    private function pathWithResponses(Path $path) : Path
    {
        foreach ($path->getTags() as $tag) {
            $className = Inflector::classify($tag);

            if ($path->hasSuccessResponseCode() || !$this->getSchemaByName($className)) {
                continue;
            }

            if ($path->getType() == 'get' && strstr($path->getOperationId(),':index')) {
                $tags = $path->getTags();
                $tag = preg_replace('/\s+/', '', reset($tags));
                $schema = (new Schema())
                    ->setName($tag)
                    ->setType('array')
                    ->setItems(['$ref' => '#/components/schemas/' . $className])
                ;
                $this->pushSchema($schema);

                $response = (new Response())->setCode(200);
                $response = $this->responseWithContent($response, '#/components/schemas/' . $tag);
                $path->pushResponse($response);
                continue;
            }

            $response = (new Response())->setCode(200);
            $response = $this->responseWithContent($response, '#/components/schemas/' . $className);
            $path->pushResponse($response);
        }

        if (!$path->hasSuccessResponseCode()) {
            $path->pushResponse((new Response())->setCode(200));
        }

        $exceptionSchema = $this->getSchemaByName($this->getConfig()->getExceptionSchema());
        if (!$exceptionSchema) {
            return $path;
        }

        foreach ($path->getResponses() as $response) {
            if ($response->getCode() < 400) {
                continue;
            }
            $path->pushResponse(
                $this->responseWithContent($response, '#/components/schemas/' . $exceptionSchema->getName())
            );
        }

        return $path;
    }

    /**
     * @param Response $response
     * @param string $schema
     * @return Response
     */
    private function responseWithContent(Response $response, string $schema) : Response
    {
        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $response->pushContent((new Content())->setMimeType($mimeType)->setSchema($schema));
        }
        return $response;
    }

    /**
     * Sets a request body on a path
     *
     * @param Path $path
     * @param ExpressiveRoute $route
     * @return Path
     */
    private function pathWithRequestBody(Path $path, ExpressiveRoute $route) : Path
    {
        $requestBody = (new RequestBodyBuilder($path, $this, $route))->build();
        if ($requestBody) {
            $path->setRequestBody($requestBody);
        }
        return $path;
    }

    /**
     * Constructs the primary array used in this class from pre-defined swagger.yml
     */
    private function buildFromDefaults() : void
    {
        $array = Yaml::parseFile($this->config->getYml());

        $array = $this->buildFromDefaultPaths($array);
        $array = $this->buildFromDefaultSchemas($array);

        $this->array = $array;
    }

    /**
     * Build paths from YML
     *
     * @todo for now an array will work, but should apply proper Path objects in the future
     * @param $array
     * @return array
     */
    private function buildFromDefaultPaths($array) : array
    {
        if (!isset($array['paths'])) {
            $array['paths'] = [];
        }

        return $array;

        /*
        foreach ($array['paths'] as $path => $operations) {

            foreach ($operations as $httpMethod => $vars) {
                $path = (new Path())
                    ->setType($httpMethod)
                    ->setSummary(isset($var['summary']) ? $var['summary'] : '')
                    ->setDescription(isset($var['description']) ? $var['description'] : '')
                    ->setTags(isset($var['tags']) ? $var['tags'] : [])
                    ->setOperationId(isset($var['operationId']) ? $var['operationId'] : '')
                    ->setDeprecated((bool) isset($var['deprecated']) ? $var['deprecated'] : false)

                if (isset($vars['externalDocs'])) {
                    $path->setExternalDocs(
                        (new OperationExternalDoc())
                            ->setDescription($vars['externalDocs']['description'])
                            ->setUrl($vars['externalDocs']['url'])
                    );
                }

                if (isset($vars['security']) && is_array($vars['security'])) {
                    foreach ($vars['security'] as $key => $scopes) {
                        $path->pushSecurity((new PathSecurity())->setName($key)->setScopes($scopes));
                    }
                }
            }
        }
        */
    }

    /**
     * Build schema from YML
     *
     * @param $array
     * @return array
     */
    private function buildFromDefaultSchemas($array) : array
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

    public function __toString(): string
    {
        return $this->toString();
    }
}