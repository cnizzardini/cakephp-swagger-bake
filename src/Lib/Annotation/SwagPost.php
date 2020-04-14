<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("type",  type = "string"),
 *   @Attribute("required",  type = "bool"),
 * })
 */
class SwagPost
{
    public $name;
    public $type;
    public $required;

    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            throw new InvalidArgumentException('Name parameter is required');
        }

        $values = array_merge(['type' => 'string', 'required' => false], $values);

        $this->name = $values['name'];
        $this->type = $values['type'];
        $this->required = $values['required'];
    }
}