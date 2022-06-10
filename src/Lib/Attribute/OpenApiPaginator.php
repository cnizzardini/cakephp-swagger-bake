<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiPaginator
{
    /**
     * @param array $sortEnum List of fields that can be sorted on, if empty the library will attempt using settings
     * from the Paginator component.
     * @param bool $useSortTextInput Use a text input for sort instead of a dropdown in Swagger UI, default: false.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly array $sortEnum = [],
        public readonly bool $useSortTextInput = false
    ) {
    }
}
