<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiSearch
{
    public string $tableClass;

    public string $collection;

    /**
     * @param string $tableClass The FQN of the table class
     * @param string $collection The CakePHP search collection
     */
    public function __construct(string $tableClass, string $collection = 'default')
    {
        $this->tableClass = $tableClass;
        $this->collection = $collection;
    }
}
