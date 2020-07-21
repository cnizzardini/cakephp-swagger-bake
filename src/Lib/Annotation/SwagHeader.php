<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Annotation for describing Header Parameter Objects.
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
 * @Attribute("explode",  type = "bool"),
 * @Attribute("style",  type = "string"),
 * @Attribute("format",  type = "string"),
 * @Attribute("example",  type = "mixed"),
 * })
 *
 * Example: Defining a custom header attribute
 *
 * `@Swag\SwagHeader(name="X-HEAD-ATTRIBUTE", type="string", description="summary")`
 *
 * ```yaml
 *      parameters:
 *        - name: X-HEAD-ATTRIBUTE
 *          in: header
 *          description: summary
 *          schema:
 *            type: string
 * ```
 *
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 * @see AbstractParameter
 */
class SwagHeader extends AbstractParameter
{
}
