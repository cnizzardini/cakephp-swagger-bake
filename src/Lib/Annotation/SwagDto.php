<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use InvalidArgumentException;

/**
 * Method level annotation for building query or form parameters from a DataTransferObject.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("class", type = "string")
 * })
 * @see https://github.com/cnizzardini/cakephp-swagger-bake for example DTO
 */
class SwagDto
{
    /**
     * The DTO class that should be parsed for request body values
     *
     * @var string
     */
    public string $class;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        if (!isset($values['class'])) {
            throw new InvalidArgumentException('Class parameter is required');
        }

        $this->class = $values['class'];
    }
}
