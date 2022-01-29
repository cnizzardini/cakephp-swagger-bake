<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

/**
 * Property or Parameter level attribute for use in your SwagDto classes.
 *
 * @see https://swagger.io/specification/ search for "Schema Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 * @see AbstractSchemaProperty
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class OpenApiResponseProperty extends AbstractSchemaProperty
{
    /**
     * @param string $type The data scalar type (e.g. string, integer)
     * @param string $format The data format (e.g. data-time, uuid)
     * @param string $description A description of the parameter
     * @param string $example An example of the parameter
     * @param array $enum Provides an enumerated list of options that can be accepted
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $name = '',
        string $type = 'string',
        string $format = '',
        string $description = '',
        string $example = '',
        bool $isDeprecated = false,
        array $enum = []
    ) {
        parent::__construct(
            name: $name,
            type: $type,
            format: $format,
            description: $description,
            example: $example,
            isDeprecated: $isDeprecated,
            enum: $enum,
        );
    }
}
