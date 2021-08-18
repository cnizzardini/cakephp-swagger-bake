<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OpenApiSchema
{
    public bool $isVisible = true;

    public bool $isPublic = true;

    public ?string $title;

    public ?string $description;

    /**
     * @param bool $isVisible is the schema visible?
     * @param bool $isPublic is the schema public?
     * @param string|null $title the title of the schema
     * @param string|null $description the description of the schema
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        bool $isVisible = true,
        bool $isPublic = true,
        ?string $title = null,
        ?string $description = null
    ) {
        $this->isVisible = $isVisible;
        $this->isPublic = $isPublic;
        $this->title = $title;
        $this->description = $description;
    }
}
