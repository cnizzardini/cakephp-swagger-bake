<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Read OpenAPI specification for exact usage of the attributes:
 *
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractSchemaProperty
{
    /**
     * @param string $name Name of the property (required)
     * @param string $type The data scalar type (e.g. string, integer)
     * @param string $format The data format (e.g. data-time, uuid)
     * @param string $title Title of the property
     * @param string $description Description of the property
     * @param string|int|float|bool $example An example value
     * @param bool $isReadOnly Is this read-only?
     * @param bool $isWriteOnly Is this write-only?
     * @param bool $isRequired Is this required?
     * @param string $default A default value
     * @param bool $nullable Is this nullable?
     * @param bool $deprecated Is this deprecated?
     * @param float|null $multipleOf Provides multiple of option, such as must be a multiple of 10
     * @param float|null $maximum A minimum value
     * @param bool $exclusiveMaximum See OpenAPI documentation
     * @param float|null $minimum A maximum value
     * @param bool $exclusiveMinimum See OpenAPI documentation
     * @param int|null $maxLength A min length
     * @param int|null $minLength A max length
     * @param string|null $pattern A regex pattern
     * @param int|null $maxItems Minimum number of items (for arrays)
     * @param int|null $minItems Maximum number of items (for arrays)
     * @param bool $uniqueItems Are the items provided required to be unique (for arrays)
     * @param int|null $maxProperties See OpenAPI documentation
     * @param int|null $minProperties See OpenAPI documentation
     * @param array $enum An enumerated list of values that can be accepted
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public string $name,
        public string $type = 'string',
        public string $format = '',
        public string $title = '',
        public string $description = '',
        public string|int|float|bool $example = '',
        public bool $isReadOnly = false,
        public bool $isWriteOnly = false,
        public bool $isRequired = false,
        public string $default = '',
        public bool $nullable = false,
        public bool $deprecated = false,
        public ?float $multipleOf = null,
        public ?float $maximum = null,
        public bool $exclusiveMaximum = false,
        public ?float $minimum = null,
        public bool $exclusiveMinimum = false,
        public ?int $maxLength = null,
        public ?int $minLength = null,
        public ?string $pattern = null,
        public ?int $maxItems = null,
        public ?int $minItems = null,
        public bool $uniqueItems = false,
        public ?int $maxProperties = null,
        public ?int $minProperties = null,
        public array $enum = [],
    ) {
    }

    /**
     * Creates an instance of SchemaProperty
     *
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    public function create(): SchemaProperty
    {
        $schemaProperty = (new SchemaProperty())
            ->setName($this->name)
            ->setDescription($this->description ?? '')
            ->setType($this->type)
            ->setFormat($this->format ?? '')
            ->setReadOnly($this->isReadOnly)
            ->setWriteOnly($this->isWriteOnly)
            ->setRequired($this->isRequired)
            ->setEnum($this->enum ?? []);

        $properties = [
            'maxLength',
            'minLength',
            'pattern',
            'maxItems',
            'minItems',
            'uniqueItems',
            'maxProperties',
            'exclusiveMaximum',
            'exclusiveMinimum',
            'uniqueItems',
            'maxProperties',
            'minProperties',
            'example',
        ];

        foreach ($properties as $property) {
            if (is_null($this->{$property})) {
                continue;
            }
            $setterMethod = 'set' . ucfirst($property);
            $schemaProperty->{$setterMethod}($this->{$property});
        }

        return $schemaProperty;
    }
}
