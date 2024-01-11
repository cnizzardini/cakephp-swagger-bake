<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch;

use ReflectionClass;
use Search\Model\Filter\Base;

/**
 * Class FilterDecorator
 *
 * @package SwaggerBake\Lib\Extension\CakeSearch
 */
class FilterDecorator
{
    private string $name;

    private string $comparison;

    /**
     * @param \Search\Model\Filter\Base $filter Filter\Base
     */
    public function __construct(Base $filter)
    {
        $this->name = $filter->name();
        $this->comparison = (new ReflectionClass($filter))->getShortName();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getComparison(): string
    {
        return $this->comparison;
    }
}
