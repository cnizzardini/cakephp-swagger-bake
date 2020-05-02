<?php

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("refEntity", type = "string"),
 *   @Attribute("httpCode", type = "integer"),
 *   @Attribute("description", type = "string"),
 *   @Attribute("mimeType", type = "string"),
 *   @Attribute("schemaType", type = "string"),
 *   @Attribute("schemaFormat", type = "string"),
 * })
 */
class SwagResponseSchema
{
    /** @var array  */
    private const DEFAULTS = [
        'refEntity' => '',
        'httpCode' => 200,
        'description' => '',
        'mimeType' => '',
        'schemaType' => '',
        'schemaFormat' => ''
    ];

    /** @var string */
    public $refEntity;

    /** @var int */
    public $httpCode = 200;

    /** @var string */
    public $description;

    /** @var string */
    public $mimeType;

    /** @var string */
    public $schemaType;

    /** @var string */
    public $schemaFormat;

    public function __construct(array $values)
    {
        $values = array_merge(SELF::DEFAULTS, $values);

        $this->refEntity = $values['refEntity'];
        $this->httpCode = intval($values['httpCode']);
        $this->description = $values['description'];
        $this->mimeType = $values['mimeType'];
        $this->schemaType = $values['schemaType'];
        $this->schemaFormat = $values['schemaFormat'];
    }
}