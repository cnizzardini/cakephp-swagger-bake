<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Describes MediaType classes
 *
 * @internal
 */
interface MediaTypeInterface
{
    /**
     * Returns a response schema
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema instance of Schema or an OpenAPI $ref string
     * @param string $schemaType array or object
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \InvalidArgumentException if $schemaType isn't array or object
     */
    public function buildSchema($schema, string $schemaType): Schema;
}
