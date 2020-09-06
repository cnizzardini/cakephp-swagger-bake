<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\OpenApiDataType;

/**
 * Read OpenAPI specification for exact usage of the attributes:
 *
 * @see http://spec.openapis.org/oas/v3.0.3#fixed-fields-9
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 */
abstract class AbstractParameter
{
    /**
     * @var string
     */
    public $ref;

    /**
     * The name of the parameter. Parameter names are case sensitive.
     *
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type = 'string';

    /**
     * A brief description of the parameter. This could contain examples of use.
     *
     * @var string
     */
    public $description = '';

    /**
     * Determines whether this parameter is mandatory.
     *
     * @var bool
     */
    public $required = false;

    /**
     * @var array
     */
    public $enum = [];

    /**
     * Specifies that a parameter is deprecated and SHOULD be transitioned out of usage. Default value is false.
     *
     * @var bool
     */
    public $deprecated = false;

    /**
     * Sets the ability to pass empty-valued parameters. This is valid only for query parameters and allows
     * sending a parameter with an empty value.
     *
     * @var bool
     */
    public $allowEmptyValue = false;

    /**
     * When this is true, parameter values of type array or object generate separate parameters for each value of
     * the array or key-value pair of the map.
     *
     * @var bool
     */
    public $explode = false;

    /**
     * Describes how the parameter value will be serialized depending on the type of the parameter value.
     *
     * @var string
     */
    public $style = '';

    /**
     * Determines whether the parameter value SHOULD allow reserved characters, as defined by [RFC3986]
     * :/?#[]@!$&'()*+,;= to be included without percent-encoding.
     *
     * @var bool
     */
    public $allowReserved = false;

    /**
     * @var string
     */
    public $format = '';

    /**
     * Example of the parameter’s potential value. The example SHOULD match the specified schema and encoding
     * properties if present.
     *
     * @var mixed
     */
    public $example;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        if (!isset($values['name']) && !isset($values['ref'])) {
            throw new InvalidArgumentException('`name` or `ref` parameter is required');
        }

        $name = $values['name'] ?? $values['ref'];

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
