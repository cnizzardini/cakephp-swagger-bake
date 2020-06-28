<?php

namespace SwaggerBake\Lib\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("isVisible", type="bool"),
 *   @Attribute("title", type="string"),
 *   @Attribute("description", type="string"),
 * })
 */
class SwagEntity
{
    /** @var bool  **/
    public $isVisible;

    /** @var string|null */
    public $title;

    /** @var string|null */
    public $description;

    public function __construct(array $values)
    {
        foreach ($values as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }
}