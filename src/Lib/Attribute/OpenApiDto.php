<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiDto
{
    /**
     * @param string $class The DTO class that should be parsed for request body values
     * @todo convert to readonly properties in PHP 8.1
     */
    public function __construct(public string $class)
    {
    }
}
