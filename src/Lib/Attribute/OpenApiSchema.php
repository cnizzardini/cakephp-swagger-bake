<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OpenApiSchema
{
    /**
     * @param bool $isVisible is the schema visible?
     * @param bool $isPublic is the schema public?
     * @param string|null $title the title of the schema
     * @param string|null $description the description of the schema
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @todo convert to readonly properties in PHP 8.1
     */
    public function __construct(
        public bool $isVisible = true,
        public bool $isPublic = true,
        public ?string $title = null,
        public ?string $description = null
    ) {
    }
}
