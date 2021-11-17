<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\PathSecurity;

/**
 * Class OperationFromYmlFactory
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationFromYmlFactory
{
    /**
     * Create Operation from YML
     *
     * @param string $httpMethod Http method i.e. PUT, POST, PATCH, GET, or DELETE
     * @param array $yaml OpenApi Operation YAML as an array
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function create(string $httpMethod, array $yaml): Operation
    {
        $operation = new Operation(
            operationId: $yaml['operationId'],
            httpMethod: $httpMethod,
            tags: $yaml['tags'] ?? [],
            isDeprecated: $yaml['deprecated'] ?? false
        );

        if (isset($yaml['externalDocs']['url'])) {
            $operation->setExternalDocs(
                (new OperationExternalDoc($yaml['externalDocs']['url'], $yaml['externalDocs']['description']))
            );
        }

        if (isset($yaml['security']) && is_array($yaml['security'])) {
            foreach ($yaml['security'] as $key => $scopes) {
                $operation->pushSecurity((new PathSecurity())->setName($key)->setScopes($scopes));
            }
        }

        return $operation;
    }
}
