<?php
declare(strict_types=1);

namespace SwaggerBake;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use SwaggerBake\Command as Commands;
use SwaggerBake\Lib\ExtensionLoader;
use SwaggerBake\Lib\Service\InstallerService;
use SwaggerBake\Lib\Service\OpenApiBakerService;

/**
 * Class Plugin
 *
 * @package SwaggerBake
 */
class Plugin extends BasePlugin
{
    /**
     * Plugin name.
     *
     * @var string
     */
    protected $name = 'SwaggerBake';

    /**
     * @var bool
     */
    protected $routes = false;

    /**
     * @var bool
     */
    protected $middleware = false;

    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        if (file_exists(CONFIG . 'swagger_bake.php')) {
            Configure::load('swagger_bake', 'default');
            ExtensionLoader::load();

            return;
        }

        if (PHP_SAPI !== 'cli') {
            triggerWarning('SwaggerBake configuration file `config/swagger_bake.php` is missing');
        }
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
