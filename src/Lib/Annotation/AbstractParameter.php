<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\OpenApiDataType;

/**
 * Class AbstractParameter
 * @package SwaggerBake\Lib\Annotation
 *
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Parameter Object"
 *
 * For `format` read OpenAPI specification on data formats:
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format
 */
abstract class AbstractParameter
{
    /** @var string */
    public $name;

    /** @var string */
    public $type = 'string';

    /** @var string */
    public $description = '';

    /** @var bool */
    public $required = false;

    /** @var array  */
    public $enum = [];

    /** @var bool */
    public $deprecated = false;

    /** @var bool */
    public $allowEmptyValue = false;

    /** @var bool */
    public $explode = false;

    /** @var string  */
    public $style = '';

    /** @var bool */
    public $allowReserved = false;

    /** @var string  */
    public $format = '';

    /** @var mixed  */
    public $example;

    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            throw new InvalidArgumentException('Name parameter is required');
        }

        $name = $values['name'];

        if (isset($values['type']) && !in_array($values['type'], OpenApiDataType::TYPES)) {
            $type = $values['type'];
            throw new SwaggerBakeRunTimeException(
                "Invalid Data Type, given [$type] for [$name] but must be one of: " .
                implode(',', OpenApiDataType::TYPES)
            );
        }

        if (isset($values['example']) && !is_string($values['example'])) {
            throw new SwaggerBakeRunTimeException(
                "Example must be a string for [$name], support for complex types may be added at a later date"
            );
        }

        foreach ($values as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }
}