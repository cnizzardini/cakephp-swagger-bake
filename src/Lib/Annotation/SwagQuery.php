<?php

namespace SwaggerBake\Lib\Annotation;

/**
 * Annotation for describing Query Parameter Objects.
 *
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Parameter Object"
 *
 * For `format` read OpenAPI specification on data formats:
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format
 *
 * @Annotation
 * @Target({"METHOD"})
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
 */
class SwagQuery extends AbstractParameter
{

}