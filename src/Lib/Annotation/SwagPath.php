<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("isVisible", type="bool"),
 * })
 */
class SwagPath
{
    public $isVisible;

    public function __construct(array $values)
    {
        $values = array_merge(['isVisible' => true], $values);
        $this->isVisible = (bool) $values['isVisible'];
    }
}