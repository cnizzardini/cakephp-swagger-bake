<?php

namespace SwaggerBake\Lib\Annotation;

/**
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Parameter Object"
 *
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("type",  type = "string"),
 *   @Attribute("description",  type = "string"),
 *   @Attribute("required",  type = "bool"),
 *   @Attribute("enum",  type = "array"),
 *   @Attribute("deprecated",  type = "bool"),
 *   @Attribute("allowReserved",  type = "bool"),
 *   @Attribute("allowEmptyValue",  type = "bool"),
 *   @Attribute("explode",  type = "bool"),
 *   @Attribute("style",  type = "string"),
 *   @Attribute("format",  type = "string"),
 *   @Attribute("example",  type = "mixed"),
 * })
 * @see AbstractParameter
 */
class SwagDtoQuery extends AbstractParameter
{

}