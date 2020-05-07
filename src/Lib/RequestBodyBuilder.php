<?php

namespace SwaggerBake\Lib;

use Cake\Utility\Inflector;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;

class RequestBodyBuilder
{
    public function __construct(Path $path, Swagger $swagger, ExpressiveRoute $route)
    {
        $this->path = $path;
        $this->route = $route;
        $this->swagger = $swagger;
    }

    /**
     * @return RequestBody|null
     */
    public function build() : ?RequestBody
    {
        $requestBody = $this->path->getRequestBody();
        if (!$requestBody) {
            $requestBody = new RequestBody();
        }

        if (!in_array($this->path->getType(), ['put','patch', 'post'])) {
            return null;
        }

        $requestBody->setRequired(true);

        return $this->requestBodyWithContent($requestBody);
    }

    /**
     * @param Schema $schema
     * @param string $tag
     * @return Schema
     */
    private function withSchemaFromModel(Schema $schema, string $tag) : Schema
    {
        $className = Inflector::classify($tag);
        if (!$this->swagger->getSchemaByName($className)) {
            return $schema;
        }

        foreach ($this->swagger->getSchemaByName($className)->getProperties() as $property) {

            if ($property->isReadOnly()) {
                continue;
            }

            $schema->pushProperty($property);
        }

        return $schema;
    }

    /**
     * @param Schema $schema
     * @return Schema
     */
    private function withSchemaFromAnnotations(Schema $schema) : Schema
    {
        $schemaProperties = (new FormData($this->route, $this->swagger->getConfig()))->getSchemaProperties();

        if (empty($schemaProperties)) {
            return $schema;
        }

        foreach ($schemaProperties as $schemaProperty) {
            $schema->pushProperty($schemaProperty);
        }

        return $schema;
    }

    /**
     * @param RequestBody $requestBody
     * @return RequestBody
     */
    private function requestBodyWithContent(RequestBody $requestBody) : RequestBody
    {
        $tags = $this->path->getTags();
        $tag = preg_replace('/\s+/', '', reset($tags));
        $tag = Inflector::singularize($tag);

        foreach ($this->swagger->getConfig()->getRequestAccepts() as $mimeType) {
            if ($mimeType == 'application/x-www-form-urlencoded') {
                $schema = new Schema();
                $schema->setType('object');
                if (!$requestBody->isIgnoreCakeSchema()) {
                    $schema = $this->withSchemaFromModel($schema, $tag);
                }
                $schema = $this->withSchemaFromAnnotations($schema);
                $requestBody->pushContent((new Content())->setMimeType($mimeType)->setSchema($schema));
                continue;
            }

            $schema = '#/components/schemas/' . $tag;
            $requestBody->pushContent((new Content())->setMimeType($mimeType)->setSchema($schema));
        }

        return $requestBody;
    }
}