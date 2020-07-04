<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("description", type = "string"),
 * @Attribute("required", type = "bool"),
 * @Attribute("ignoreCakeSchema", type = "bool"),
 * })
 */
class SwagRequestBody
{
    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $required;

    /**
     * @var bool
     */
    public $ignoreCakeSchema;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        $values = array_merge(['description' => '', 'required' => true, 'ignoreCakeSchema' => false], $values);
        $this->description = $values['description'];
        $this->required = (bool)$values['required'];
        $this->ignoreCakeSchema = (bool)$values['ignoreCakeSchema'];
    }
}
