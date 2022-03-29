<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiOperation
{
    /**
     * A method level attribute for defining a subset of OA Operation values. Additional values are configured by
     * parsing Doc Block comments from the controller action, see main documentation.
     *
     * @param string|null $summary A summary (i.e. title), setting to null prevents summary being set from doc blocks
     * @param string|null $description A description, setting to null prevents description being set from doc blocks
     * @param bool $isVisible Is this operation visible
     * @param string[] $tagNames An array of OpenAPI tags
     * @param bool $isDeprecated Is the operation deprecated?
     * @param array|null $externalDocs An optional external docs array
     * @param int|null $sortOrder The order the operation appears at in OpenAPI output. Defaults to the order the action
     * appears in the controller class.
     * @see https://mixerapi.com/plugins/cakephp-swagger-bake/docs/attributes/#OpenApiOperation
     * @see https://spec.openapis.org/oas/latest.html#operation-object
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @todo convert to readonly properties in PHP 8.1
     */
    public function __construct(
        public ?string $summary = '',
        public ?string $description = '',
        public bool $isVisible = true,
        public array $tagNames = [],
        public bool $isDeprecated = false,
        public ?array $externalDocs = null,
        public ?int $sortOrder = null,
    ) {
    }
}
