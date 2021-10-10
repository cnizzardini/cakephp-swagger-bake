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
}
