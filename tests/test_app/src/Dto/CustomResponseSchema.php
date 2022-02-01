<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;
use SwaggerBake\Lib\OpenApi\CustomSchemaInterface;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class CustomResponseSchema implements CustomSchemaInterface
{
    #[OpenApiSchemaProperty(name: 'name', type: 'string', example: 'Paul')]
    public string $name;

    /**
     * @inheritDoc
     */
    public static function getOpenApiSchema(): Schema
    {
        return (new Schema())
            ->setName('Custom')
            ->setTitle('Custom Title')
            ->setProperties([
                new SchemaProperty('age', 'integer', 'int32', null, 32)
            ]);
    }
}