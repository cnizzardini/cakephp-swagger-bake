<?php


namespace SwaggerBake\Lib;

use Cake\Routing\Route\Route;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class RequestBodyBuilder
{
    public function __construct(Path $path, Swagger $swagger, Route $route)
    {
        $this->path = $path;
        $this->route = $route;
        $this->swagger = $swagger;
    }

    public function build() : ?RequestBody
    {
        foreach ($this->path->getTags() as $tag) {

            if (!in_array($this->path->getType(), ['put','patch', 'post'])) {
                continue;
            }

            $schema = new Schema();
            $schema->setType('object');
            $schema = $this->withSchemaFromModel($schema, $tag);
            $schema = $this->withSchemaFromAnnotations($schema);
            break;
        }

        if (!isset($schema)) {
            return null;
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

    private function withSchemaFromModel(Schema $schema, string $tag) : Schema
    {
        $className = Inflector::classify($tag);
        if (!$this->swagger->getSchemaByName($className)) {
            return $schema;
        }

        foreach ($this->swagger->getSchemaByName($className)->getProperties() as $propertyName => $property) {

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

        return $schema;
    }

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
}