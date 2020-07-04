<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("name", type = "string"),
 * @Attribute("scopes",  type = "array")
 * })
 */
class SwagSecurity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $scopes;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        if (!isset($values['name'])) {
            throw new InvalidArgumentException('Name parameter is required');
        }

        $values = array_merge(['scopes' => []], $values);

        $this->name = $values['name'];
        $this->scopes = $values['scopes'];
    }
}
