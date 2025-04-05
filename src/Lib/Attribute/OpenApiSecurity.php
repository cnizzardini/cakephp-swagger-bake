<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class OpenApiSecurity
{
    /**
     * Method level attribute to define security requirements on an OA Operation. The name is required and must
     * match an OA Security Scheme Object.
     *
     * @param string $name The name of the security schema object defined in your OpenApi YAML.
     * @param array $scopes The available scopes for the OAuth2 security scheme.
     * @see https://mixerapi.com/plugins/cakephp-swagger-bake/docs/attributes/#OpenApiPath
     * @see https://spec.openapis.org/oas/latest.html#operation-object
     */
    public function __construct(
        public readonly string $name,
        public readonly array $scopes = [],
    ) {
    }
}
