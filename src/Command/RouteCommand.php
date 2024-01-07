<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Routing\Router;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Route\RouteScanner;

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
            ->addOption('config', [
                'help' => 'Configuration (defaults to config/swagger_bake). Example: OtherApi.swagger_bake',
                'default' => 'swagger_bake',
            ])
            ->addOption('prefix', [
                'help' => 'The route prefix (uses value in configuration by default)',
            ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): void
    {
        $this->loadConfig($args);

        $io->hr();
        $io->out('| SwaggerBake is checking your routes...');
        $io->hr();

        $output = [
            ['Route name', 'URI template', 'Method(s)', 'Controller', 'Action', 'Plugin'],
        ];

        $config = new Configuration();

        $prefix = $args->getOption('prefix');
        if (!empty($prefix) && is_string($prefix)) {
            $config->setPrefix($prefix);
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
