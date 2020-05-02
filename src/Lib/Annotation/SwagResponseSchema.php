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
 * })
 */
class SwagResponseSchema
{
    /** @var string */
    public $refEntity;

    /** @var int */
    public $httpCode = 200;

    /** @var string */
    public $description;

    public function __construct(array $values)
    {
        $values = array_merge(['refEntity' => '','httpCode' => 200, 'description' => ''], $values);

        $this->refEntity = $values['refEntity'];
        $this->httpCode = intval($values['httpCode']);
        $this->description = $values['description'];
    }
}