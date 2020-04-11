<?php


namespace SwaggerBake\Lib\Utility;


use Cake\Core\Configure;
use Cake\Routing\Router;
use SwaggerBake\Lib\CakeRoute;
use LogicException;

class ValidateConfiguration
{
    public static function validate()
    {
        $ymlFile = getcwd() . Configure::read('SwaggerBake.yml');

        if (empty($ymlFile) || !strstr($ymlFile, '.yml')) {
            throw new LogicException('Yml file is required');
        }

        if (!file_exists($ymlFile)) {
            throw new LogicException('Yml file not found, try specifying full path to file');
        }

        $prefix = Configure::read('SwaggerBake.prefix');

        if (empty($prefix)) {
            throw new LogicException('Prefix is required');
        }

        $cakeRoute = new CakeRoute(new Router(), $prefix);
        $routes = $cakeRoute->getRoutes();

        if (empty($routes)) {
            $io->out("<warning>No routes were found for: $prefix</warning>");
            return;
        }

        $output = getcwd() . Configure::read('SwaggerBake.json');;

        if (!file_exists($output) && !touch($output)) {
            throw new LogicException(
                'Unable to create swagger file. Try creating an empty file first or checking permissions'
            );
        }
    }
}