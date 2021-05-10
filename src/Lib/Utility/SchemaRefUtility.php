<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Swagger;

class SchemaRefUtility
{
    /**
     * Returns path to OpenAPI schema $ref such as #/components/Schemas/EntityName. This will first check if the
     * supplied $ref exists, if not it will return the OpenAPI default schema $ref.
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema The Schema instance
     * @param \SwaggerBake\Lib\Swagger $swagger The Swagger instance
     * @param string $ref The $ref to search for, such as #/x-swagger-bake/components/schemas/Entity-Read
     * @return string
     */
    public static function whichRef(Schema $schema, Swagger $swagger, string $ref): string
    {
        $openApi = $swagger->getArray();
        $paths = explode('/', $ref);
        array_shift($paths);
        $found = 0;

        foreach ($paths as $path) {
            if (isset($openApi[$path])) {
                $openApi = $openApi[$path];
                $found++;
            }
        }

        if ($found === count($paths)) {
            return $ref;
        }

        return '#/components/schemas/' . $schema->getName();
    }
}
