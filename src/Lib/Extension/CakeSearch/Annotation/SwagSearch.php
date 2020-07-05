<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch\Annotation;

use InvalidArgumentException;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("tableClass", type = "string"),
 * @Attribute("collection", type = "string")
 * })
 */
class SwagSearch
{
    /**
     * @var string
     */
    public $tableClass;

    /**
     * @var string
     */
    public $collection;

    /**
     * @param array $values Key-value pair of annotation attributes
     */
    public function __construct(array $values)
    {
        if (!isset($values['tableClass'])) {
            throw new InvalidArgumentException('tableClass parameter is required');
        }

        $this->tableClass = $values['tableClass'];
        $this->collection = $values['collection'] ?? 'default';
    }
}
