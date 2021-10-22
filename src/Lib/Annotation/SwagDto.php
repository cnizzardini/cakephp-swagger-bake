<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @deprecated Use OpenApiDto instead
 * @codeCoverageIgnore
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("class", type = "string")
 * })
 */
class SwagDto
{
    /**
     * @var string
     */
    public $class;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        if (!isset($values['class'])) {
            throw new InvalidArgumentException('Class parameter is required');
        }

        $this->class = $values['class'];
    }
}
