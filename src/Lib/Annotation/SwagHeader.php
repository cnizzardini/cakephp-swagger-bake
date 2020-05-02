<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("type",  type = "string"),
 *   @Attribute("description",  type = "string"),
 *   @Attribute("required",  type = "bool"),
 * })
 */
class SwagHeader
{
    /** @var string */
    public $name;

    /** @var string */
    public $type;

    /** @var string */
    public $description;

    /** @var bool */
    public $required;

    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            throw new InvalidArgumentException('Name parameter is required');
        }

        $values = array_merge(['type' => 'string', 'description' => '', 'required' => false], $values);

        $this->name = $values['name'];
        $this->type = $values['type'];
        $this->description = $values['description'];
        $this->required = (bool) $values['required'];
    }
}