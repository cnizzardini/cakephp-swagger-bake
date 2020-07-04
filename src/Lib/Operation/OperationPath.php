<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Class OperationPath
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationPath
{
    /**
     * Adds Path parameters to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator $route RouteDecorator
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithPathParameters(Operation $operation, RouteDecorator $route): Operation
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
