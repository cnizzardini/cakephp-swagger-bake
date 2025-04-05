<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use InvalidArgumentException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Read OpenAPI specification for exact usage:
 *
 * @see http://spec.openapis.org/oas/v3.0.3#fixed-fields-9
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 */
abstract class AbstractOpenApiParameter
{
    /**
     * @param string $name Name of the query parameter, required unless $ref is defined
     * @param string $ref The OpenAPI $ref, required unless name is defined
     * @param string $type The data scalar type (e.g. string, integer)
     * @param string $format The data format (e.g. data-time, uuid)
     * @param string $description A description of the parameter
     * @param string|float|int|bool $example An example scalar value of the parameter
     * @param bool $isRequired Is the parameter required?
     * @param array $enum An enumerated list of values.
     * @param bool $isDeprecated Is the parameter deprecated?
     * @param bool $allowEmptyValue Allow empty values?
     * @param bool $explode Explode on comma?
     * @param string $style See OpenAPI documentation
     * @param bool $allowReserved See OpenAPI documentation
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public readonly string $name = '',
        public readonly string $ref = '',
        public readonly string $type = 'string',
        public readonly string $format = '',
        public readonly string $description = '',
        public readonly string|bool|int|float $example = '',
        public readonly bool $isRequired = false,
        public readonly array $enum = [],
        public readonly bool $isDeprecated = false,
        public readonly bool $allowEmptyValue = false,
        public readonly bool $explode = false,
        public readonly string $style = '',
        public readonly bool $allowReserved = false,
    ) {
        if (empty($name) && empty($ref)) {
            throw new InvalidArgumentException('One of `name` or `ref` is required for ' . self::class);
        }
    }

    /**
     * Creates an instance of Parameter from an AbstractParameter annotation
     *
     * @return \SwaggerBake\Lib\OpenApi\Parameter
     */
    public function create(): Parameter
    {
        switch (static::class) {
            case OpenApiHeader::class:
                $parameter = new Parameter('header', $this->ref, $this->name);
                break;
        }

        if (!isset($parameter)) {
            throw new SwaggerBakeRunTimeException('Parameter object was not created');
        }

        $parameter
            ->setRef($this->ref ?? '')
            ->setName($this->name ?? '')
            ->setDescription($this->description)
            ->setRequired($this->isRequired)
            ->setDeprecated($this->isDeprecated)
            ->setStyle($this->style)
            ->setExplode($this->explode)
            ->setExample($this->example)
            ->setSchema(
                (new Schema())
                    ->setType($this->type)
                    ->setEnum($this->enum)
                    ->setFormat($this->format),
            );

        return $parameter;
    }
}
