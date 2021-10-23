<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * @deprecated Use OpenApiDtoRequestBody
 * @codeCoverageIgnore
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
 */
class SwagDtoForm extends AbstractSchemaProperty
{
    /**
     * @param array $values Annotation values
     * @deprecated This class annotation will be deprecated in a future version, please use SwagDtoRequestBody
     */
    public function __construct(array $values)
    {
        parent::__construct($values);
        deprecationWarning(
            'This class annotation will be deprecated in a future version, please use SwagDtoRequestBody'
        );
    }
}
