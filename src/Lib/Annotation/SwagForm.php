<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Method level annotation for adding form data fields.
 *
 * For use with application/x-www-form-urlencoded request bodies.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("name", type = "string"),
 * @Attribute("type",  type = "string"),
 * @Attribute("format",  type = "string"),
 * @Attribute("description",  type = "string"),
 * @Attribute("readOnly",  type = "bool"),
 * @Attribute("writeOnly",  type = "bool"),
 * @Attribute("required",  type = "bool"),
 * @Attribute("multipleOf", type = "float"),
 * @Attribute("maximum",  type = "float"),
 * @Attribute("exclusiveMaximum",  type = "bool"),
 * @Attribute("minimum",  type = "float"),
 * @Attribute("exclusiveMinimum",  type = "bool"),
 * @Attribute("maxLength",  type = "integer"),
 * @Attribute("minLength", type = "integer"),
 * @Attribute("pattern",  type = "string"),
 * @Attribute("maxItems",  type = "integer"),
 * @Attribute("minItems",  type = "integer"),
 * @Attribute("uniqueItems",  type = "bool"),
 * @Attribute("maxProperties",  type = "integer"),
 * @Attribute("minProperties", type = "integer"),
 * @Attribute("enum", type = "array"),
 * })
 *
 * Example: Field that is an integer that must be a between 10 and 100 and a multiple of 10
 *
 * `@Swag\SwagForm(name="field", type="integer", minimum=10, maximum=100, multipleOf=10)`
 *
 * ```yaml
 *         field:
 *           type: integer
 *           minimum: 5
 *           maximum: 100
 *           multipleOf: 10
 * ```
 *
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Schema Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 * @see AbstractSchemaProperty
 */
class SwagForm extends AbstractSchemaProperty
{
}
