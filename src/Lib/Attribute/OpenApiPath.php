<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class OpenApiPath
{
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
     * @todo convert to readonly properties in PHP 8.1
     */
    public function __construct(
        public bool $isVisible = true,
        public ?string $ref = null,
        public ?string $description = null,
        public ?string $summary = null
    ) {
    }
}
