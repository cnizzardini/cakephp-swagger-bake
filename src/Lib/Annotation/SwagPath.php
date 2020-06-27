<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("isVisible", type="bool"),
 *   @Attribute("ref", type="string"),
 *   @Attribute("description", type="string"),
 *   @Attribute("summary", type="string"),
 * })
 */
class SwagPath
{
    /** @var bool */
    public $isVisible = true;

    /** @var string|null */
    public $ref;

    /** @var string|null */
    public $description;

    /** @var string|null */
    public $summary;

    public function __construct(array $values)
    {
        foreach ($values as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }
}