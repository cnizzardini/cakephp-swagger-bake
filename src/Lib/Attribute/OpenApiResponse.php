<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OpenApiResponse
{
    /**
     * @param string $schemaType The type data to be returned, typically object or an array, but can be string. For
     *      instance, an operation returning a single resource (e.g. view() actions) would be an object, while
     *      operations returning a collect would (e.g. index() actions) would be an array. Defaults to object.
     * @param string $statusCode The HTTP status code this response represents.
     * @param string|null $ref An optional existing $ref from your OpenAPI YAML. If not set, the entity
     *      associated with your controller per cakephp convention will be assumed.
     * @param string|null $schema An optional FQN (e.g. '\App\MyResponse') to a class with an OpenApiSchema attribute
     *      that describes the response.
     * @param string|null $description An optional response description.
     * @param array|null $mimeTypes An optional array of mime types, if none are given then the defaults from your
     *      swagger_bake config are used.
     * @param array|null $associations Configuration for displaying a resources associations. See documentation.
     * @param string|null $schemaFormat This is really only applicable for schemaTypes of string.
     * @todo convert to readonly properties in PHP 8.1
     */
    public function __construct(
        public string $schemaType = 'object',
        public string $statusCode = '200',
        public ?string $ref = null,
        public ?string $schema = null,
        public ?string $description = null,
        public ?array $mimeTypes = null,
        public ?array $associations = null,
        public ?string $schemaFormat = null,
    ) {
        $this->ref = $ref ?? '';
        $this->description = $description ?? '';
        $this->mimeTypes = $mimeTypes ?? null;

        $this->schemaFormat = $schemaFormat ?? '';
        if (is_array($associations)) {
            $this->associations = array_replace(
                ['table' => null, 'whiteList' => null],
                $associations
            );
        }
    }
}
