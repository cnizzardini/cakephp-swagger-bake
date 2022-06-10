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
     */
    public function __construct(
        public readonly string $tableClass,
        public readonly string $collection = 'default'
    ) {
    }
}
