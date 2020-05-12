<?php

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagHeader;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

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

        foreach ($swagHeaders as $annotation) {
            $parameter = (new Parameter())
                ->setName($annotation->name)
                ->setDescription($annotation->description)
                ->setAllowEmptyValue(false)
                ->setDeprecated(false)
                ->setRequired($annotation->required)
                ->setIn('header')
                ->setSchema((new Schema())->setType($annotation->type))
            ;

            $operation->pushParameter($parameter);
        }

        return $operation;
    }
}