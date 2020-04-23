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

class RouteCommand extends Command
{
    /**
     * List Cake Routes that can be added to Swagger. Prints to console.
     *
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->hr();
        $io->out("| SwaggerBake is checking your routes...");
        $io->hr();

        ValidateConfiguration::validate();

        $output = [
            ['Route name', 'URI template', 'Defaults'],
        ];

        $config = new Configuration();
        $prefix = $config->getPrefix();
        $cakeRoute = new CakeRoute(new Router(), $config);
        $routes = $cakeRoute->getRoutes();

        if (empty($routes)) {
            $io->out();
            $io->warning("No routes were found for: $prefix");
            $io->out("Have you added RESTful routes? Do you have models associated with those routes?");
            $io->out();
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
