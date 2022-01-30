<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

/**
 * Property or Parameter level attribute for use in your SwagDto classes. Read the comments to see all supported
 * properties.
 *
 * Read OpenAPI specification for exact usage of the attributes:
 *
 * @see https://swagger.io/specification/ search for "Schema Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 * @see AbstractSchemaProperty
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class OpenApiDtoRequestBody extends AbstractSchemaProperty
{
}
