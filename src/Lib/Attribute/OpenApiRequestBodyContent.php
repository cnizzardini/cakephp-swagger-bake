<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiRequestBodyContent
{
    public string $ref;

    public array $mimeTypes;

    /**
     * @param string $ref OpenAPI $ref
     * @param array $mimeTypes A list of mime types (i.e. application/json, application/xml)
     */
    public function __construct(string $ref, array $mimeTypes = [])
    {
        $this->ref = $ref;
        $this->mimeTypes = $mimeTypes;
    }
}
