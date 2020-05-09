<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("refEntity", type = "string"),
 *   @Attribute("mimeType", type = "string")
 * })
 */
class SwagRequestBodyContent
{
    /** @var string */
    public $refEntity;

    /** @var string */
    public $mimeType;

    public function __construct(array $values)
    {
        $this->refEntity = $values['refEntity'];
        $this->mimeType = $values['mimeType'];
    }
}