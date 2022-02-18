<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;
use SwaggerBake\Lib\OpenApi\CustomSchemaInterface;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

#[OpenApiSchema]
#[OpenApiSchemaProperty(name: 'age', type: 'integer', example: 32)]
class CustomResponseSchemaPublic
{
    #[OpenApiSchemaProperty(name: 'name', type: 'string', example: 'Paul')]
    public string $name;
}