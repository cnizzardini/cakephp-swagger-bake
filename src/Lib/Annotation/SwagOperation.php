<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("isVisible", type="bool"),
 * })
 */
class SwagOperation
{
    public $isVisible;

    public function __construct(array $values)
    {
        $values = array_merge(['isVisible' => true], $values);
        $this->isVisible = (bool) $values['isVisible'];
    }
}