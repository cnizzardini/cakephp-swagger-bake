<?php

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagSecurity;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\PathSecurity;

/**
 * Class OperationSecurity
 * @package SwaggerBake\Lib\Operation
 */
class OperationSecurity
{
    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    public function getOperationWithSecurity(Operation $operation, array $annotations) : Operation
    {
        $swagSecurities = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagSecurity;
        });

        foreach ($swagSecurities as $annotation) {
            $operation->pushSecurity(
                (new PathSecurity())
                    ->setName($annotation->name)
                    ->setScopes($annotation->scopes)
            );
        }

        return $operation;
    }
}