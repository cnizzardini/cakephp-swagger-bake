<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiSearch
{
    /**
     * @param string $tableClass The FQN of the table class
     * @param string $collection The CakePHP search collection
     * @todo convert to readonly properties in php 8.1
     */
    public function __construct(
        public string $tableClass,
        public string $collection = 'default'
    ) {
    }
}
