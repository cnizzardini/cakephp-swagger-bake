<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Routing\Router;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * @class SwaggerBakeCommand
 * @package SwaggerBake
 * Generates a swagger json file
 */
class BakeCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out("Running...");

        ValidateConfiguration::validate();

        $ymlFile = getcwd() . Configure::read('SwaggerBake.yml');
        $prefix = Configure::read('SwaggerBake.prefix');
        $output = getcwd() . Configure::read('SwaggerBake.json');;

        $cakeRoute = new CakeRoute(new Router(), $prefix);
        $routes = $cakeRoute->getRoutes();

        if (empty($routes)) {
            $io->out("<warning>No routes were found for: $prefix</warning>");
            return;
        }

        $swagger = new Swagger(
            $ymlFile,
            new CakeModel($cakeRoute, $prefix)
        );

        file_put_contents($output, $swagger->toString());

        $io->out("<success>Swagger File Created: $output</success>");
    }
}
