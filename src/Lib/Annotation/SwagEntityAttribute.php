<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("type",  type = "string"),
 *   @Attribute("readOnly",  type = "bool"),
 *   @Attribute("writeOnly",  type = "bool"),
 *   @Attribute("required",  type = "bool"),
 * })
 */
class SwagEntityAttribute
{
    public $name;
    public $type;
    public $readOnly;
    public $writeOnly;
    public $required;

    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            throw new InvalidArgumentException('Name parameter is required');
        }

        $values = array_merge(
            ['type' => 'string', 'readOnly' => false, 'writeOnly' => false, 'required' => false],
            $values
        );

        $this->name = $values['name'];
        $this->type = $values['type'];
        $this->readOnly = $values['readOnly'];
        $this->writeOnly = $values['writeOnly'];
        $this->required = $values['required'];
    }
}