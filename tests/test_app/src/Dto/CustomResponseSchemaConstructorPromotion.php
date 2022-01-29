<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;

#[OpenApiSchema]
class CustomResponseSchemaConstructorPromotion
{
    public function __construct(
        #[OpenApiSchemaProperty(name: 'name', type: 'string', example: 'Paul')]
        public string $name,
        #[OpenApiSchemaProperty(name: 'age', type: 'integer', format: 'int32', example: 32)]
        public int $age
    ){
    }
}