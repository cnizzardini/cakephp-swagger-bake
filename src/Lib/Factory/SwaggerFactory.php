<?php

namespace SwaggerBake\Lib\Factory;

use Cake\Routing\Router;
use LogicException;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * Class SwaggerFactory
 * @package SwaggerBake\Lib\Factory
 *
 * Creates an instance of SwaggerBake\Lib\Swagger
 */
class SwaggerFactory
{
    /**
     * Factory for Swagger objects
     *
     * @return Swagger
     */
    public function create() : Swagger
    {
        ValidateConfiguration::validate();

        $config = new Configuration();
        $prefix = $config->getPrefix();

        $cakeRoute = new CakeRoute(new Router(), $config);
        $routes = $cakeRoute->getRoutes();

        if (empty($routes)) {
            throw new LogicException("No routes were found for: $prefix");
        }

        return new Swagger(
            new CakeModel($cakeRoute, $config)
        );
    }
}