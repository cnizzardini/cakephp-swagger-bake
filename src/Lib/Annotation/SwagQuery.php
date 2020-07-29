<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Annotation for describing Query Parameter Objects.
 *
 * @Annotation
 * @Target({"METHOD"})
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
 * Example: Define a string field that will be exploded on
 *
 * `@Swag\SwagQuery(name="field", type="string", explode=true, description="Explodes on commas")`
 *
 * ```yaml
 *         field:
 *           description: Explodes on commas
 *           type: string
 *           explode: true
 * ```
 *
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 * @see AbstractParameter
 */
class SwagQuery extends AbstractParameter
{
}
