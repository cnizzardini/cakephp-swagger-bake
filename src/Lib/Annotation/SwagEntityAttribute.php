<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * Annotation for describing Entity properties.
 *
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Schema Object"
 *
 * For `format` read OpenAPI specification on data formats:
 * @see https://swagger.io/docs/specification/data-models/data-types
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("type",  type = "string"),
 *   @Attribute("format",  type = "string"),
 *   @Attribute("description",  type = "string"),
 *   @Attribute("readOnly",  type = "bool"),
 *   @Attribute("writeOnly",  type = "bool"),
 *   @Attribute("required",  type = "bool"),
 *   @Attribute("multipleOf", type = "float"),
 *   @Attribute("maximum",  type = "float"),
 *   @Attribute("exclusiveMaximum",  type = "bool"),
 *   @Attribute("minimum",  type = "float"),
 *   @Attribute("exclusiveMinimum",  type = "bool"),
 *   @Attribute("maxLength",  type = "integer"),
 *   @Attribute("minLength", type = "integer"),
 *   @Attribute("pattern",  type = "string"),
 *   @Attribute("maxItems",  type = "integer"),
 *   @Attribute("minItems",  type = "integer"),
 *   @Attribute("uniqueItems",  type = "bool"),
 *   @Attribute("maxProperties",  type = "integer"),
 *   @Attribute("minProperties", type = "integer"),
 *   @Attribute("enum", type = "array"),
 * })
 */
class SwagEntityAttribute
{
    /** @var string */
    public $name;

    /** @var string */
    public $type = 'string';

    /** @var string */
    public $format;

    /** @var string */
    public $description;

    /** @var bool */
    public $readOnly = false;

    /** @var bool */
    public $writeOnly = false;

    /** @var bool */
    public $required = false;

    /** @var string */
    public $title;

    /** @var mixed */
    public $default;

    /** @var bool */
    public $nullable = false;

    /** @var bool */
    public $deprecated = false;

    /** @var float|null */
    public $multipleOf;

    /** @var float|null */
    public $maximum;

    /** @var bool */
    public $exclusiveMaximum = false;

    /** @var float|null */
    public $minimum;

    /** @var bool */
    public $exclusiveMinimum = false;

    /** @var int|null */
    public $maxLength;

    /** @var int|null */
    public $minLength;

    /** @var string|null */
    public $pattern;

    /** @var int|null */
    public $maxItems;

    /** @var int|null */
    public $minItems;

    /** @var bool */
    public $uniqueItems = false;

    /** @var int|null */
    public $maxProperties;

    /** @var int|null */
    public $minProperties;

    /** @var array */
    public $enum = [];

    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            throw new InvalidArgumentException('Name parameter is required');
        }

        foreach ($values as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->{$attribute} = $value;
            }
        }
    }
}