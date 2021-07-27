<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Swagger;

/**
 * Abstract class for MediaTypes
 */
abstract class AbstractMediaType
{
    protected Swagger $swagger;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema|string
     */
    protected $schema;

    /**
     * The OpenAPI $ref for the Schema
     *
     * @var string|null
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
            $this->schema = $schema; //$swagger->getSchemaByName($read) ?? $swagger->getSchemaByName($name);
        } elseif (is_string($schema)) {
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

    /**
     * @param string $schemaType must be array or object
     * @return void
     */
    protected function validateSchemaType(string $schemaType): void
    {
        if (!in_array($schemaType, ['array', 'object'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument must be array or object but was given schemaType `%s`. If you\'re using the ' .
                    'SwagResponseSchema annotation, try defining schemaType.',
                    $schemaType
                )
            );
        }
    }
}
