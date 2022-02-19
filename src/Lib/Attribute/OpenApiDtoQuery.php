<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

/**
 * Property or Parameter level attribute for use in your SwagDto classes.
 *
 * For use with HTTP GET requests requiring query parameters. Use this in your DTO class (e.g. App\Dto|MyDto)
 *
 * Read OpenAPI specification for exact usage of the attributes
 *
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format  search for "data format"
 * @see AbstractOpenApiParameter
 * @deprecated This will be removed in v3.0.0
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class OpenApiDtoQuery extends AbstractOpenApiParameter
{
    /**
     * @param string $name Name of the query parameter, required unless $ref is defined
     * @param string $ref The OpenAPI $ref, required unless name is defined
     * @param string $type The data scalar type (e.g. string, integer)
     * @param string $format The data format (e.g. data-time, uuid)
     * @param string $description A description of the parameter
     * @param string $example An example of the parameter
     * @param bool $allowEmptyValue Allow empty values?
     * @param bool $explode Explode on comma?
     * @param bool $isRequired Is the parameter required?
     * @param bool $isDeprecated Is the parameter deprecated?
     * @param bool $allowReserved Allow reserved words?
     * @param array $enum Provides an enumerated list of options that can be accepted
     * @param string $style See OpenAPI documentation
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name = '',
        string $ref = '',
        string $type = 'string',
        string $format = '',
        string $description = '',
        string $example = '',
        bool $allowEmptyValue = false,
        bool $explode = false,
        bool $isRequired = false,
        bool $isDeprecated = false,
        bool $allowReserved = false,
        array $enum = [],
        string $style = '',
    ) {
        parent::__construct(
            name: $name,
            ref: $ref,
            type: $type,
            format: $format,
            description: $description,
            example: $example,
            isRequired: $isRequired,
            enum: $enum,
            isDeprecated: $isDeprecated,
            allowEmptyValue: $allowEmptyValue,
            explode: $explode,
            style: $style,
            allowReserved: $allowReserved
        );
    }
}