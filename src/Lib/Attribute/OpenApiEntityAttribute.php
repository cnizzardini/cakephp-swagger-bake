<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

/**
 * Class level attribute for customizing Schema Attributes.
 *
 * Use this in your Entity classes (i.e. App\Model\Entity)
 *
 * Read OpenAPI specification for exact usage of the attributes:
 *
 * @see https://swagger.io/specification/ search for "Schema Object"
 * @see https://swagger.io/docs/specification/data-models/data-types search for "data formats"
 * @see AbstractSchemaProperty
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class OpenApiEntityAttribute extends AbstractSchemaProperty
{
    /**
     * @param string $name Name of the property (required)
     * @param string $type The data scalar type (e.g. string, integer)
     * @param string $format The data format (e.g. data-time, uuid)
     * @param string $title Title of the property
     * @param string $description Description of the property
     * @param string $example An example value
     * @param bool $readOnly Is this read-only?
     * @param bool $writeOnly Is this write-only?
     * @param bool $required Is this required?
     * @param string $default A default value
     * @param bool $nullable Is this nullable?
     * @param bool $deprecated Is this deprecated?
     * @param float|null $multipleOf Provides multiple of option, such as must be a multiple of 10
     * @param float|null $minimum A minimum value
     * @param bool $exclusiveMinimum See OpenAPI documentation
     * @param float|null $maximum A maximum value
     * @param bool $exclusiveMaximum See OpenAPI documentation
     * @param int|null $minLength A min length
     * @param int|null $maxLength A max length
     * @param string|null $pattern A regex pattern
     * @param int|null $minItems Minimum number of items (for arrays)
     * @param int|null $maxItems Maximum number of items (for arrays)
     * @param bool $uniqueItems Are the items provided required to be unique (for arrays)
     * @param int|null $minProperties See OpenAPI documentation
     * @param int|null $maxProperties See OpenAPI documentation
     * @param array $enum An enumerated list of values that can be accepted
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name,
        string $type = 'string',
        string $format = '',
        string $title = '',
        string $description = '',
        string $example = '',
        bool $readOnly = false,
        bool $writeOnly = false,
        bool $required = false,
        string $default = '',
        bool $nullable = false,
        bool $deprecated = false,
        ?float $multipleOf = null,
        ?float $minimum = null,
        bool $exclusiveMinimum = false,
        ?float $maximum = null,
        bool $exclusiveMaximum = false,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
        ?int $minItems = null,
        ?int $maxItems = null,
        bool $uniqueItems = false,
        ?int $minProperties = null,
        ?int $maxProperties = null,
        array $enum = []
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->format = $format;
        $this->title = $title;
        $this->description = $description;
        $this->example = $example;
        $this->readOnly = $readOnly;
        $this->writeOnly = $writeOnly;
        $this->required = $required;
        $this->default = $default;
        $this->nullable = $nullable;
        $this->deprecated = $deprecated;
        $this->multipleOf = $multipleOf;
        $this->maximum = $maximum;
        $this->exclusiveMaximum = $exclusiveMaximum;
        $this->minimum = $minimum;
        $this->exclusiveMinimum = $exclusiveMinimum;
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
        $this->pattern = $pattern;
        $this->minItems = $minItems;
        $this->maxItems = $maxItems;
        $this->uniqueItems = $uniqueItems;
        $this->minProperties = $minProperties;
        $this->maxProperties = $maxProperties;
        $this->enum = $enum;
    }
}
