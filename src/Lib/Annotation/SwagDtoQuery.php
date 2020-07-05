<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Annotation for creating DTO query parameters
 *
 * Read OpenAPI specification for exact usage of the attributes:
 *
 * @see https://swagger.io/specification/ search for "Parameter Object"
 *
 * For `format` read OpenAPI specification on data formats:
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 * @Attribute("name", type = "string"),
 * @Attribute("type",  type = "string"),
 * @Attribute("description",  type = "string"),
 * @Attribute("required",  type = "bool"),
 * @Attribute("enum",  type = "array"),
 * @Attribute("deprecated",  type = "bool"),
 * @Attribute("allowReserved",  type = "bool"),
 * @Attribute("allowEmptyValue",  type = "bool"),
 * @Attribute("explode",  type = "bool"),
 * @Attribute("style",  type = "string"),
 * @Attribute("format",  type = "string"),
 * @Attribute("example",  type = "mixed"),
 * })
 * @see AbstractParameter
 */
class SwagDtoQuery extends AbstractParameter
{
}
