<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiDto
{
    public string $class;

    /**
     * @param string $class The DTO class that should be parsed for request body values
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }
}
