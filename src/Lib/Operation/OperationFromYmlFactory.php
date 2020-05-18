<?php

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\PathSecurity;

/**
 * Class OperationFromYmlFactory
 * @package SwaggerBake\Lib\Operation
 */
class OperationFromYmlFactory
{
    /**
     * Create Operation from YML
     *
     * @param string $httpMethod
     * @param array $var
     * @return Operation
     */
    public function create(string $httpMethod, array $var) : Operation
    {
        $operation = (new Operation())
            ->setHttpMethod($httpMethod)
            ->setTags(isset($var['tags']) ? $var['tags'] : [])
            ->setOperationId(isset($var['operationId']) ? $var['operationId'] : '')
            ->setDeprecated((bool) isset($var['deprecated']) ? $var['deprecated'] : false);

        if (isset($var['externalDocs']['url'])) {
            $operation->setExternalDocs(
                (new OperationExternalDoc())
                    ->setDescription(
                        isset($var['externalDocs']['description']) ? $var['externalDocs']['description'] : ''
                    )
                    ->setUrl($var['externalDocs']['url'])
            );
        }

        if (isset($var['security']) && is_array($var['security'])) {
            foreach ($var['security'] as $key => $scopes) {
                $operation->pushSecurity((new PathSecurity())->setName($key)->setScopes($scopes));
            }
        }

        return $operation;
    }
}