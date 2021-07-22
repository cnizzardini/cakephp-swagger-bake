<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;

interface MediaTypeInterface
{
    /**
     * Returns a response schema
     *
     * @param string $schemaType array or object
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \InvalidArgumentException if $schemaType isn't array or object
     */
    public function buildSchema(string $schemaType): Schema;
}
