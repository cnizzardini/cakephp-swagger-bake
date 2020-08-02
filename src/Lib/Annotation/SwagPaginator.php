<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Method level annotation for adding CakePHP Paginator query parameters: page, limit, sort, and direction.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("sortEnum", type="array"),
 * @Attribute("useSortTextInput", type="bool")
 * })
 * @see https://book.cakephp.org/4/en/controllers/components/pagination.html
 */
class SwagPaginator
{
    /**
     * List of fields that can be sorted on
     *
     * @var array
     */
    public $sortEnum;

    /**
     * Set to true to only display sort as a text-input (non-enumerated string)
     *
     * @var bool
     */
    public $useSortTextInput = false;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        $values = array_merge(['sortEnum' => [], 'useSortTextInput' => false], $values);
        $this->sortEnum = $values['sortEnum'];
        $this->useSortTextInput = $values['useSortTextInput'];
    }
}
