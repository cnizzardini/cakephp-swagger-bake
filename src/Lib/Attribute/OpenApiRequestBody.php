<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;
use SwaggerBake\Lib\OpenApi\RequestBody;

/**
 * @todo needs better documentation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class OpenApiRequestBody
{
    /**
     * @param string $ref OpenAPI $ref
     * @param string $description Request body description
     * @param array $mimeTypes A list of mime types (i.e. application/json, application/xml)
     * @param bool $required Is the request body required?
     * @param bool $ignoreCakeSchema Ignore assigning schema automatically?
     * @todo convert to readonly properties in PHP 8.1
     */
    public function __construct(
        public string $ref = '',
        public string $description = '',
        public array $mimeTypes = [],
        public bool $required = true,
        public bool $ignoreCakeSchema = false
    ) {
    }

    /**
     * Create RequestBody
     *
     * @param \SwaggerBake\Lib\OpenApi\RequestBody|null $requestBody An optional RequestBody to build on
     * @return \SwaggerBake\Lib\OpenApi\RequestBody
     */
    public function createRequestBody(?RequestBody $requestBody = null): RequestBody
    {
        return ($requestBody ?? new RequestBody())
            ->setDescription($this->description)
            ->setRequired($this->required);
    }
}
