<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use InvalidArgumentException;
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
     * @param string|bool|int|float $example An example scalar value of the parameter
     * @param bool $required Is the parameter required?
     * @param array $enum An enumerated list of values.
     * @param bool $deprecated Is the parameter deprecated?
     * @param bool $allowEmptyValue Allow empty values?
     * @param bool $explode Explode on comma?
     * @param string $style See OpenAPI documentation
     * @param bool $allowReserved See OpenAPI documentation
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        public string $name = '',
        public string $ref = '',
        public string $type = 'string',
        public string $format = '',
        public string $description = '',
        public string|bool|int|float $example = '',
        public bool $required = false,
        public array $enum = [],
        public bool $deprecated = false,
        public bool $allowEmptyValue = false,
        public bool $explode = false,
        public string $style = '',
        public bool $allowReserved = false,
    ) {
        if (empty($name) && empty($ref)) {
            throw new InvalidArgumentException('One name or ref is required for ' . self::class);
        }
    }

    /**
     * Creates an instance of Parameter from an AbstractParameter annotation
     *
     * @return \SwaggerBake\Lib\OpenApi\Parameter
     */
    public function create(): Parameter
    {
        $parameter = (new Parameter())
            ->setRef($this->ref ?? '')
            ->setName($this->name ?? '')
            ->setDescription($this->description)
            ->setRequired($this->required)
            ->setDeprecated($this->deprecated)
            ->setStyle($this->style)
            ->setExplode($this->explode)
            ->setExample($this->example)
            ->setSchema(
                (new Schema())
                    ->setType($this->type)
                    ->setEnum($this->enum)
                    ->setFormat($this->format)
            );

        switch (static::class) {
            case OpenApiDtoQuery::class:
                $parameter
                    ->setIn('query')
                    ->setAllowReserved($this->allowReserved)
                    ->setAllowEmptyValue($this->allowEmptyValue);
                break;
            case OpenApiHeader::class:
                $parameter->setIn('header');
                break;
        }

        return $parameter;
    }
}
