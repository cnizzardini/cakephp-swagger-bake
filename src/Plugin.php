<?php
declare(strict_types=1);

namespace SwaggerBake;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use SwaggerBake\Command as Commands;
use SwaggerBake\Lib\ExtensionLoader;
use SwaggerBake\Lib\Service\InstallerService;
use SwaggerBake\Lib\Service\OpenApiBakerService;
use SwaggerBake\Lib\Service\OpenApiControllerService;

class Plugin extends BasePlugin
{
    protected bool $routes = false;

    protected bool $middleware = false;

    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        ExtensionLoader::load();
    }

    /**
     * @inheritDoc
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('swagger routes', Commands\RouteCommand::class);
        $commands->add('swagger bake', Commands\BakeCommand::class);
        $commands->add('swagger models', Commands\ModelCommand::class);
        $commands->add('swagger install', Commands\InstallCommand::class);

        return $commands;
    }

    /**
     * @inheritDoc
     */
    public function services(ContainerInterface $container): void
    {
        parent::services($container);

        $container->add(OpenApiControllerService::class);

        if (PHP_SAPI === 'cli') {
            $container->add(OpenApiBakerService::class);
            $container->add(InstallerService::class);

            $container
                ->add(Commands\BakeCommand::class)
                ->addArgument(OpenApiBakerService::class);

            $container
                ->add(Commands\InstallCommand::class)
                ->addArgument(InstallerService::class);
        }
    }
}
