<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("isVisible", type="bool"),
 *   @Attribute("tagNames", type="array"),
 * })
 */
class SwagOperation
{
    /** @var bool */
    public $isVisible;

    /** @var string[] */
    public $tagNames;

    public function __construct(array $values)
    {
        $values = array_merge(['isVisible' => true, 'tagNames' => []], $values);
        $this->isVisible = (bool) $values['isVisible'];
        $this->tagNames = $values['tagNames'];
    }
}