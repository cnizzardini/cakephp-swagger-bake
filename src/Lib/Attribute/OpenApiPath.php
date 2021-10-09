<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OpenApiPath
{
    public bool $isVisible = true;

    public ?string $ref;

    public ?string $description;

    public ?string $summary;

    /**
     * Class level attribute to define scalar OA Path values.
     *
     * @param bool $isVisible Should this path be visible in OpenAPI output?
     * @param string|null $ref An OpenAPI ref such as `#/paths/my-path`
     * @param string|null $description Overwrites the default description
     * @param string|null $summary Overwrites the default summary (if any)
     * @see https://mixerapi.com/plugins/cakephp-swagger-bake/docs/attributes/#OpenApiPath
     * @see https://spec.openapis.org/oas/latest.html#path-item-object
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
