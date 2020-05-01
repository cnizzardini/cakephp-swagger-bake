<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("type",  type = "string"),
 *   @Attribute("description",  type = "string"),
 *   @Attribute("readOnly",  type = "bool"),
 *   @Attribute("writeOnly",  type = "bool"),
 *   @Attribute("required",  type = "bool"),
 * })
 */
class SwagEntityAttribute
{
    /** @var string */
    public $name;

    /** @var string */
    public $type;

    /** @var string */
    public $description;

    /** @var bool */
    public $readOnly;

    /** @var bool */
    public $writeOnly;

    /** @var bool */
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
        $this->description = $values['description'];
        $this->readOnly = $values['readOnly'];
        $this->writeOnly = $values['writeOnly'];
        $this->required = $values['required'];
    }
}