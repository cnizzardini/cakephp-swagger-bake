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
 *   @Attribute("required",  type="boolean"),
 * })
 */
class SwagQuery
{
    public $name;
    public $type;
    public $required;

    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            throw new InvalidArgumentException('Name parameter is required');
        }

        $type = strtolower($values['type']);

        if (!in_array($type, OpenApiDataType::TYPES)) {
            throw new SwaggerBakeRunTimeException(
                "Invalid Data Type, given `$type` but must be one of: " .
                implode(',', OpenApiDataType::TYPES)
            );
        }

        $values = array_merge(['type' => $type, 'required' => false], $values);

        $this->name = $values['name'];
        $this->type = $type;
        $this->required = $values['required'];
    }
}