<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

/**
 * @deprecated Use OpenApiPathParameter
 * @codeCoverageIgnore
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("name", type = "string"),
 * @Attribute("type",  type = "string"),
 * @Attribute("description",  type = "string"),
 * @Attribute("allowReserved",  type = "bool"),
 * @Attribute("format",  type = "string"),
 * @Attribute("example",  type = "mixed"),
 * })
 */
class SwagPathParameter extends AbstractParameter
{
    /**
     * @param array $values annotation values
     */
    public function __construct(array $values)
    {
        $values['required'] = true;
        if (!in_array($values['type'], ['string','integer'])) {
            throw new SwaggerBakeRunTimeException('Path parameter must be an integer or string');
        }

        parent::__construct($values);
    }
}
