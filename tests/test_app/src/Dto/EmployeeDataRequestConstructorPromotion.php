<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\Attribute\OpenApiQueryParam;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;

class EmployeeDataRequestConstructorPromotion
{
    public function __construct(
        #[OpenApiSchemaProperty(name: 'first_name', description: 'testing')]
        #[OpenApiQueryParam(name: 'first_name', description: 'testing')]
        public string $firstName,
        #[OpenApiSchemaProperty(name: 'last_name', description: 'testing')]
        #[OpenApiQueryParam(name: 'last_name', description: 'testing')]
        public string $lastName,
        #[OpenApiSchemaProperty(name: 'title', description: 'testing')]
        #[OpenApiQueryParam(name: 'title', description: 'testing')]
        public string $title,
        #[OpenApiSchemaProperty(name: 'age', type: 'integer', format: 'int32', description: 'testing')]
        #[OpenApiQueryParam(name: 'age', type: 'integer', format: 'int32', description: 'testing')]
        public string $age,
        #[OpenApiSchemaProperty(name: 'date', type: 'string', format: 'date', description: 'testing')]
        #[OpenApiQueryParam(name: 'date', type: 'string', format: 'date', description: 'testing')]
        public string $date
    ) {
    }
}
