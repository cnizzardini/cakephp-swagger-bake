<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiSearch
{
    /**
     * @param string|null $tableClass The FQN of the table class
     * @param string $collection The CakePHP search collection
     * @param string|null $alias The table alias
     * @param array $options An array of options to pass into TableLocator::get()
     * @todo convert to readonly properties in php 8.1
     * @todo drop $tableClass from constructor and re-organize properties in v3.0.0
     */
    public function __construct(
        public ?string $tableClass = null,
        public string $collection = 'default',
        public ?string $alias = null,
        public array $options = [],
    ) {
        if ($this->tableClass !== null) {
            trigger_deprecation(
                'cnizzardini/cakephp-swagger-bake',
                'v2.4.2',
                'tableClass will be removed in a future version, use alias instead',
            );
        }
    }
}
