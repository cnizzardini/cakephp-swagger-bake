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
        public readonly string $name,
        public readonly string $type = 'string',
        public readonly ?string $format = null,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly mixed $example = null,
        public readonly ?string $default = null,
        public readonly bool $isReadOnly = false,
        public readonly bool $isWriteOnly = false,
        public readonly bool $isRequired = false,
        public readonly bool $isNullable = false,
        public readonly bool $isDeprecated = false,
        public readonly ?float $multipleOf = null,
        public readonly ?float $minimum = null,
        public readonly bool $isExclusiveMinimum = false,
        public readonly ?float $maximum = null,
        public readonly bool $isExclusiveMaximum = false,
        public readonly ?int $minLength = null,
        public readonly ?int $maxLength = null,
        public readonly ?string $pattern = null,
        public readonly ?int $minItems = null,
        public readonly ?int $maxItems = null,
        public readonly bool $hasUniqueItems = false,
        public readonly ?int $minProperties = null,
        public readonly ?int $maxProperties = null,
        public readonly array $enum = [],
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
