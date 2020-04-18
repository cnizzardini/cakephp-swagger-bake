<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("description", type = "string"),
 *   @Attribute("required", type = "bool"),
 *   @Attribute("ignoreCakeSchema", type = "bool"),
 * })
 */
class SwagRequestBody
{
    public $description;
    public $required;
    public $ignoreCakeSchema;

    public function __construct(array $values)
    {
        $values = array_merge(['description' => '', 'required' => true, 'ignoreCakeSchema' => false], $values);
        $this->description = $values['description'];
        $this->required = (bool) $values['required'];
        $this->ignoreCakeSchema = (bool) $values['ignoreCakeSchema'];
    }
}