<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Swagger;

abstract class AbstractMediaType
{
    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema|string
     */
    protected $schema;

    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    protected $swagger;

    /**
     * The OpenAPI $ref for the Schema
     *
     * @var string
     */
    protected $ref;

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema instance of Schema or an openapi $ref string
     * @param \SwaggerBake\Lib\Swagger $swagger instance of Swagger
     */
    public function __construct($schema, Swagger $swagger)
    {
        $this->swagger = $swagger;

        if ($schema instanceof Schema) {
            $read = $schema->getReadSchemaName();
            $name = $schema->getName();
            $this->schema = $swagger->getSchemaByName($read) ?? $swagger->getSchemaByName($name);
            $this->ref = $this->schema->getRefPath();
        } elseif (is_string($schema)) {
            $this->schema = $schema;
            $this->ref = $schema;
        } else {
            throw new \InvalidArgumentException(
                '$schema argument must be instance of Schema or an OpenAPI $ref string'
            );
        }
    }

    /**
     * Determines the name of the element that contains the collections items
     *
     * @param array $openapi openapi array
     * @return string
     */
    protected function whichData(array $openapi): string
    {
        if (!isset($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'])) {
            return 'data';
        }

        if ($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'] instanceof Schema) {
            /** @var \SwaggerBake\Lib\OpenApi\Schema $schema */
            $schema = $openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'];
            $array = $schema->toArray();

            return $array['x-data-element'] ?? 'data';
        }

        if (is_array($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'])) {
            return $openapi['x-swagger-bake']['components']['schemas']['Generic-Collection']['x-data-element'];
        }

        return 'data';
    }
}
