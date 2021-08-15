<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OpenApiPath
{
    use AttributeTrait;

    public bool $isVisible = true;

    public ?string $ref;

    public ?string $description;

    public ?string $summary;

    /**
     * @param bool $isVisible Should this path be visible in OpenAPI output?
     * @param string|null $ref An OpenAPI ref such as `#/paths/my-path`
     * @param string|null $description Overwrites the default description
     * @param string|null $summary Overwrites the default summary (if any)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        bool $isVisible = true,
        ?string $ref = null,
        ?string $description = null,
        ?string $summary = null
    ) {
        $this->isVisible = $isVisible;
        $this->ref = $ref;
        $this->description = $description;
        $this->summary = $summary;
    }
}
