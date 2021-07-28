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
 * @todo this may need more documentation and examples
 */
class SwagSecurity
{
    public string $name;

    /**
     * The available scopes for the OAuth2 security scheme.
     *
     * @var array
     */
    public array $scopes;

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
