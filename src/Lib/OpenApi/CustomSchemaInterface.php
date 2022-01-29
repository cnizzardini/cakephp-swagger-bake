<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\OpenApi;

interface CustomSchemaInterface
{
    /**
     * Describe your own OpenAPI schema.
     *
     * @see \SwaggerBake\Lib\OpenApi\Schema
     * @see \SwaggerBake\Lib\OpenApi\SchemaProperty
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    public static function getOpenApiSchema(): Schema;
}
