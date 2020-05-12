<?php

namespace SwaggerBake\Lib\Operation;

use Cake\Utility\Inflector;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Operation;

class OperationFromRouteFactory
{
    /**
     * Creates a basic operation without request or response body, etc..
     *
     * @param ExpressiveRoute $route
     * @param string $httpMethod
     * @return Operation|null
     */
    public function create(ExpressiveRoute $route, string $httpMethod) : ?Operation
    {
        if (empty($route->getMethods())) {
            return null;
        }

        return (new Operation())
            ->setHttpMethod(strtolower($httpMethod))
            ->setOperationId($route->getName())
            ->setTags([
                Inflector::humanize(Inflector::underscore($route->getController()))
            ])
        ;
    }
}