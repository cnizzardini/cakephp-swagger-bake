<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\OpenApiDataType;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type="string"),
 *   @Attribute("type",  type="string"),
 *   @Attribute("description",  type="string"),
 *   @Attribute("required",  type="boolean"),
 * })
 */
class SwagQuery
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

        if (!in_array($values['type'], OpenApiDataType::TYPES)) {
            $type = $values['type'];
            $name = $values['name'];
            throw new SwaggerBakeRunTimeException(
                "Invalid Data Type, given [$type] for [$name] but must be one of: " .
                implode(',', OpenApiDataType::TYPES)
            );
        }

        $this->name = $values['name'];
        $this->type = $values['type'];
        $this->description = $values['description'];
        $this->required = (bool) $values['required'];
    }
}