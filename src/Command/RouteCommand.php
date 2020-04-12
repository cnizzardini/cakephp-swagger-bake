<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Routing\Router;
use InvalidArgumentException;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * @class CakeRouteCommand
 * @package SwaggerBake
 * Generates a list of routes matching a prefix
 */
class RouteCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out("Running...");

        ValidateConfiguration::validate();

        $output = [
            ['Route name', 'URI template', 'Defaults'],
        ];

        $config = new Configuration();
        $prefix = $config->getPrefix();

        $cakeRoute = new CakeRoute(new Router(), $prefix);
        $routes = $cakeRoute->getRoutes();

        if (empty($routes)) {
            $io->out("<warning>No routes were found for: $prefix</warning>");
            return;
        }

        foreach ($routes as $route) {
            $name = $route->options['_name'] ?? $route->getName();
            ksort($route->defaults);
            $output[] = [$name, $route->template, json_encode($route->defaults)];
        }

        $io->helper('table')->output($output);
    }
}
