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
     * @param string|null $summary Overwrites the default summary (if any)
     * @param string|null $description Overwrites the default description
     * @param string[] $tags Sets the tags for all operations in the path. Tags set on individual operations will take
     *  precedence.
     * @see https://spec.openapis.org/oas/latest.html#path-item-object
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        public readonly bool $isVisible = true,
        public readonly ?string $ref = null,
        public readonly ?string $summary = null,
        public readonly ?string $description = null,
        public readonly array $tags = []
    ) {
    }
}
