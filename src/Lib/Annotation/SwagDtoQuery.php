<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * @deprecated Use OpenApiDtoQuery
 * @codeCoverageIgnore
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
 */
class SwagDtoQuery extends AbstractParameter
{
}
