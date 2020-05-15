<?php

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;

class OperationPath
{
    /**
     * @param Operation $operation
     * @param RouteDecorator $route
     * @return Operation
     */
    public function getOperationWithPathParameters(Operation $operation, RouteDecorator $route) : Operation
    {
        $pieces = explode('/', $route->getTemplate());
        $results = array_filter($pieces, function ($piece) {
            return substr($piece, 0, 1) == ':' ? true : null;
        });

        foreach ($results as $result) {

            $name = strtolower($result);

            if (substr($name, 0, 1) == ':') {
                $name = substr($name, 1);
            }

            $operation->pushParameter(
                (new Parameter())
                    ->setName($name)
                    ->setAllowEmptyValue(false)
                    ->setDeprecated(false)
                    ->setRequired(true)
                    ->setIn('path')
                    ->setSchema((new Schema())->setType('string'))
            );
        }

        return $operation;
    }
}