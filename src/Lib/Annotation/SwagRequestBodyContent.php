<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("refEntity", type = "string"),
 * @Attribute("mimeTypes", type = "array"),
 * @Attribute("mimeType", type = "string")
 * })
 */
class SwagRequestBodyContent
{
    /**
     * OpenApi Components.Schema
     *
     * @var string
     * @example #/components/schemas/Actor
     */
    public $refEntity;

    /**
     * List of mimeTypes accepted as request bodies
     *
     * @var array
     * @example {"application/json","application/xml"}
     */
    public $mimeTypes;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        $values = array_merge(['refEntity' => '', 'mimeTypes' => []], $values);

        $this->refEntity = $values['refEntity'];
        $this->mimeTypes = $values['mimeTypes'];
    }
}
