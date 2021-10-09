<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiPaginator
{
    public array $sortEnum = [];

    public bool $useSortTextInput = false;

    /**
     * @param array $sortEnum List of fields that can be sorted on, if empty the library will attempt using settings
     * from the Paginator component.
     * @param bool $useSortTextInput Use a text input for sort instead of a dropdown in Swagger UI, default: false.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(array $sortEnum = [], bool $useSortTextInput = false)
    {
        $this->sortEnum = $sortEnum;
        $this->useSortTextInput = $useSortTextInput;
    }
}
