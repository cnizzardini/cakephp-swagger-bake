<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Routing\Router;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\RouteScanner;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * Class RouteCommand
 *
 * @package SwaggerBake\Command
 */
class RouteCommand extends Command
{
    use CommandTrait;

    /**
     * @param \Cake\Console\ConsoleOptionParser $parser ConsoleOptionParser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('SwaggerBake Route Checker')
            ->addOption('prefix', [
                'help' => 'The route prefix (uses value in configuration by default)',
            ]);

        return $parser;
    }

    /**
     * List Cake Routes that can be added to Swagger. Prints to console.
     *
     * @param \Cake\Console\Arguments $args Arguments
     * @param \Cake\Console\ConsoleIo $io ConsoleIo
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->loadConfig();

        $io->hr();
        $io->out('| SwaggerBake is checking your routes...');
        $io->hr();

        $output = [
            ['Route name', 'URI template', 'Method(s)', 'Controller', 'Action', 'Plugin'],
        ];

        $config = new Configuration();
        ValidateConfiguration::validate($config);

        if (!empty($args->getOption('prefix'))) {
            $config->set('prefix', $args->getOption('prefix'));
        }

        $prefix = $config->getPrefix();
        $routeScanner = new RouteScanner(new Router(), $config);
        $routes = $routeScanner->getRoutes();

        if (empty($routes)) {
            $io->out();
            $io->warning("No routes were found for: $prefix");
            $io->out('Have you added RESTful routes? Do you have models associated with those routes?');
            $io->out();
            $this->abort();
        }

        foreach ($routes as $route) {
            $output[] = [
                $route->getName(),
                $route->getTemplate(),
                implode(', ', $route->getMethods()),
                $route->getController(),
                $route->getAction(),
                $route->getPlugin(),
            ];
        }

        $io->helper('table')->output($output);
    }
}
