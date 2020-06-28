<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * Annotation for specifying a DTO class
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("class", type = "string")
 * })
 */
class SwagDto
{
    /** @var string */
    public $class;

    public function __construct(array $values)
    {
        if (!isset($values['class'])) {
            throw new InvalidArgumentException('Class parameter is required');
        }

        $this->class = $values['class'];
    }
}