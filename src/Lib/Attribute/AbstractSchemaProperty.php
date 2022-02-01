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
     * @param string|null $format The data format (e.g. data-time, uuid)
     * @param string|null $title Title of the property
     * @param string|null $description Description of the property
     * @param mixed|null $example An example value
     * @param string|null $default A default value
     * @param bool $isReadOnly Is this read-only?
     * @param bool $isWriteOnly Is this write-only?
     * @param bool $isRequired Is this required?
     * @param bool $isNullable Is this nullable?
     * @param bool $isDeprecated Is this deprecated?
     * @param float|null $multipleOf Provides multiple of option, such as must be a multiple of 10
     * @param float|null $minimum A maximum value
     * @param bool $isExclusiveMinimum See OpenAPI documentation
     * @param float|null $maximum A minimum value
     * @param bool $isExclusiveMaximum See OpenAPI documentation
     * @param int|null $minLength A max length
     * @param int|null $maxLength A min length
     * @param string|null $pattern A regex pattern
     * @param int|null $minItems Maximum number of items (for arrays)
     * @param int|null $maxItems Minimum number of items (for arrays)
     * @param bool $hasUniqueItems See OpenAPI documentation
     * @param int|null $minProperties See OpenAPI documentation
     * @param int|null $maxProperties See OpenAPI documentation
     * @param array $enum An enumerated list of values that can be accepted
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public string $name,
        public string $type = 'string',
        public ?string $format = null,
        public ?string $title = null,
        public ?string $description = null,
        public mixed $example = null,
        public ?string $default = null,
        public bool $isReadOnly = false,
        public bool $isWriteOnly = false,
        public bool $isRequired = false,
        public bool $isNullable = false,
        public bool $isDeprecated = false,
        public ?float $multipleOf = null,
        public ?float $minimum = null,
        public bool $isExclusiveMinimum = false,
        public ?float $maximum = null,
        public bool $isExclusiveMaximum = false,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $pattern = null,
        public ?int $minItems = null,
        public ?int $maxItems = null,
        public bool $hasUniqueItems = false,
        public ?int $minProperties = null,
        public ?int $maxProperties = null,
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
            'maxProperties',
            'isExclusiveMaximum',
            'isExclusiveMinimum',
            'hasUniqueItems',
            'maxProperties',
            'minProperties',
            'example',
        ];

        foreach ($properties as $property) {
            if (is_null($this->{$property})) {
                continue;
            }

            $setterMethod = 'set' . ucfirst($property);
            foreach (['is', 'has'] as $propertyPrefix) {
                if (str_starts_with($property, $propertyPrefix)) {
                    $setterMethod = 'set' . ucfirst(substr($property, strlen($propertyPrefix)));
                    break;
                }
            }

            $schemaProperty->{$setterMethod}($this->{$property});
        }

        return $schemaProperty;
    }
}
