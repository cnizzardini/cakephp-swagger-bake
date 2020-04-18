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
    public $refEntity;
    public $httpCode;
    public $description;

    public function __construct(array $values)
    {
        if (!isset($values['refEntity']) && $values['refEntity'] != null) {
            throw new InvalidArgumentException('refEntity parameter is required');
        }

        $values = array_merge(['httpCode' => 200, 'description' => ''], $values);

        $this->refEntity = $values['refEntity'];
        $this->httpCode = intval($values['httpCode']);
        $this->description = $values['description'];
    }
}