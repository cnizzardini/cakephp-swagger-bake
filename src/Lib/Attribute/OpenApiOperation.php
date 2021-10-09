<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiOperation
{
    public bool $isVisible = true;

    public array $tagNames = [];

    public bool $isPut = false;

    /**
     * A method level attribute for defining a subset of OA Operation values. Additional values are configured by
     * parsing Doc Block comments from the controller action, see main documentation.
     *
     * @param bool $isVisible Is this operation visible
     * @param string[] $tagNames An array of tags
     * @param bool $isPut Use HTTP PUT instead of PATCH on controller::edit crud action, default is false (PATCH)
     * @see https://mixerapi.com/plugins/cakephp-swagger-bake/docs/attributes/#OpenApiOperation
     * @see https://spec.openapis.org/oas/latest.html#operation-object
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(bool $isVisible = true, array $tagNames = [], bool $isPut = false)
    {
        $this->isVisible = $isVisible;
        $this->tagNames = $tagNames;
        $this->isPut = $isPut;
    }
}
