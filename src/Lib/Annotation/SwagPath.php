<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 * @Attribute("isVisible", type="bool"),
 * @Attribute("ref", type="string"),
 * @Attribute("description", type="string"),
 * @Attribute("summary", type="string"),
 * })
 */
class SwagPath
{
    /**
     * @var bool
     */
    public $isVisible = true;

    /**
     * @var string|null
     */
    public $ref;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $summary;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        foreach ($values as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }
}
