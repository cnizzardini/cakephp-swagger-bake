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
    public string $name;

    public string $type = 'string';

    public string $format = '';

    public string $title = '';

    public string $description = '';

    public bool $readOnly = false;

    public bool $writeOnly = false;

    public bool $required = false;

    public string $default = '';

    public bool $nullable = false;

    public bool $deprecated = false;

    public ?float $multipleOf;

    public ?float $maximum;

    public bool $exclusiveMaximum = false;

    public ?float $minimum;

    public bool $exclusiveMinimum = false;

    public ?int $maxLength;

    public ?int $minLength;

    public ?string $pattern;

    public ?int $maxItems;

    public ?int $minItems;

    public bool $uniqueItems = false;

    public ?int $maxProperties;

    public ?int $minProperties;

    public array $enum = [];

    /**
     * @var mixed
     */
    public $example;

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
            ->setReadOnly($this->readOnly ?? false)
            ->setWriteOnly($this->writeOnly ?? false)
            ->setRequired($this->required ?? false)
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
