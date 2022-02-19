<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\Attribute\OpenApiDtoQuery;
use SwaggerBake\Lib\Attribute\OpenApiDtoRequestBody;

/**
 * @deprecated remove in v3.0.0
 */
class EmployeeDataRequestConstructorPromotionLegacy
{
    public function __construct(
        #[OpenApiDtoRequestBody(name: 'first_name', description: 'testing')]
        #[OpenApiDtoQuery(name: 'first_name', description: 'testing')]
        public string $firstName,
        #[OpenApiDtoRequestBody(name: 'last_name', description: 'testing')]
        #[OpenApiDtoQuery(name: 'last_name', description: 'testing')]
        public string $lastName,
        #[OpenApiDtoRequestBody(name: 'title', description: 'testing')]
        #[OpenApiDtoQuery(name: 'title', description: 'testing')]
        public string $title,
        #[OpenApiDtoRequestBody(name: 'age', type: 'integer', format: 'int32', description: 'testing')]
        #[OpenApiDtoQuery(name: 'age', type: 'integer', format: 'int32', description: 'testing')]
        public string $age,
        #[OpenApiDtoRequestBody(name: 'date', type: 'string', format: 'date', description: 'testing')]
        #[OpenApiDtoQuery(name: 'date', type: 'string', format: 'date', description: 'testing')]
        public string $date
    ) {
    }
}
