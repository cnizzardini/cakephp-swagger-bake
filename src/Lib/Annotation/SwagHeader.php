<?php

namespace SwaggerBake\Lib\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("type",  type = "string"),
 *   @Attribute("description",  type = "string"),
 *   @Attribute("required",  type = "bool"),
 *   @Attribute("enum",  type = "array"),
 * })
 */
class SwagHeader extends AbstractParameter
{

}