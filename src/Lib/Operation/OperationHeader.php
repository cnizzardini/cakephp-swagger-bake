<?php

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagHeader;
use SwaggerBake\Lib\Factory\ParameterFromAnnotationFactory;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Class OperationHeader
 * @package SwaggerBake\Lib\Operation
 */
class OperationHeader
{
    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    public function getOperationWithHeaders(Operation $operation, array $annotations) : Operation
    {
        $swagHeaders = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagHeader;
        });

        $factory = new ParameterFromAnnotationFactory();
        foreach ($swagHeaders as $annotation) {
            $operation->pushParameter($factory->create($annotation));
        }

        return $operation;
    }
}