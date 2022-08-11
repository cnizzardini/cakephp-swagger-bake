<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiSearch
{
    /**
     * @param string $alias The table alias, see `TableLocator::get($alias)`
     * @param string $collection The CakePHP search collection
     * @param array $options An array of options to pass into `TableLocator::get($alias, $options)`
     */
    public function __construct(
        public readonly string $alias,
        public string $collection = 'default',
        public array $options = [],
    ) {
    }
}
