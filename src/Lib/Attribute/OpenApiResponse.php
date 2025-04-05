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
     * @param class-string|null $schema An optional FQN (e.g. '\App\MyResponse') to a class with an OpenApiSchema attribute
     *      that describes the response.
     * @param string|null $description An optional response description.
     * @param array|null $mimeTypes An optional array of mime types, if none are given then the defaults from your
     *      swagger_bake config are used.
     * @param array|null $associations Configuration for displaying a resources associations. See documentation.
     * @param string|null $schemaFormat This is really only applicable for schemaTypes of string.
     */
    public function __construct(
        public readonly string $schemaType = 'object',
        public readonly string $statusCode = '200',
        public readonly ?string $ref = null,
        public readonly ?string $schema = null,
        public readonly ?string $description = null,
        public readonly ?array $mimeTypes = null,
        public ?array $associations = null,
        public readonly ?string $schemaFormat = null,
    ) {
        if (is_array($associations)) {
            $this->associations = array_replace(
                ['table' => null, 'whiteList' => null],
                $associations,
            );
        }
    }
}
