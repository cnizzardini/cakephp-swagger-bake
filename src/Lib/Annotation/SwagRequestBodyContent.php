<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("refEntity", type = "string"),
 * @Attribute("mimeType", type = "string")
 * })
 */
class SwagRequestBodyContent
{
    /**
     * @var string
     */
    public $refEntity;

    /**
     * @var string
     */
    public $mimeType;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        $this->refEntity = $values['refEntity'];
        $this->mimeType = $values['mimeType'];
    }
}
