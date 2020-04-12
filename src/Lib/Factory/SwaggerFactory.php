<?php


namespace SwaggerBake\Lib\Factory;


use Cake\Routing\Router;
use LogicException;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

class SwaggerFactory
{
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