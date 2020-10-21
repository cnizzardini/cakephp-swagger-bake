<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Property level annotation for use in your SwagDto classes. Read the comments to see all supported properties.
 *
 * For use with application/x-www-form-urlencoded requests. Use this in your DTO class (e.g. App\Dto|MyDto)
 *
 * @Annotation
 * @Target({"PROPERTY"})
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
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Schema Object"
 * @see https://swagger.io/docs/specification/data-models/data-types/?sbsearch=Data%20Format search for "data format"
 * @see AbstractSchemaProperty
 * @deprecated
 */
class SwagDtoForm extends AbstractSchemaProperty
{
    /**
     * @param array $values Annotation values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        deprecationWarning(
            'This class annotation will be deprecated in a future version, please use SwagDtoRequestBody'
        );
    }
}
