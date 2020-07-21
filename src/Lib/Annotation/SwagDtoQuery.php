<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Property level annotation for use in your SwagDto classes.
 *
 * For use with HTTP GET requests requiring query parameters. Use this in your DTO class (e.g. App\Dto|MyDto)
 *
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
 *
 * Read OpenAPI specification for exact usage of the attributes
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format  search for "data format"
 * @see AbstractParameter
 */
class SwagDtoQuery extends AbstractParameter
{
}
