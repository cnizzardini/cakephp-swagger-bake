<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension\CakeSearch;

use ReflectionClass;
use Search\Model\Filter\Base;
use Search\Model\Filter\Boolean;
use SwaggerBake\Lib\Utility\OpenApiDataType;

/**
 * Class FilterDecorator
 *
 * @package SwaggerBake\Lib\Extension\CakeSearch
 */
class FilterDecorator
{
    private Base $filter;

    private string $name;

    private string $comparison;

    /**
     * @param \Search\Model\Filter\Base $filter Filter\Base
     * @throws \ReflectionException
     */
    public function __construct(Base $filter)
    {
        $this->filter = $filter;
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
