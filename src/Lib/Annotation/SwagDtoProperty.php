<?php

namespace SwaggerBake\Lib\Annotation;

/**
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
 *
 * @see DtoParser::getSchemaProperties() for items supported by body requests
 * @see DtoParser::getParameters() for items supported by query requests
 */
class SwagDtoProperty extends AbstractParameter
{

}