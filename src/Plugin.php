<?php
declare(strict_types=1);

namespace SwaggerBake;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;
use SwaggerBake\Command as Commands;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\ExtensionLoader;

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
     * @param \Cake\Core\PluginApplicationInterface $app PluginApplicationInterface
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        if (file_exists(CONFIG . 'swagger_bake.php')) {
            Configure::load('swagger_bake', 'default');
            AnnotationLoader::load();
            ExtensionLoader::load();

            return;
        }

        if (PHP_SAPI !== 'cli') {
            triggerWarning('SwaggerBake configuration file `config/swagger_bake.php` is missing');
        }
    }

    /**
     * @param \Cake\Console\CommandCollection $commands CommandCollection
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('swagger routes', Commands\RouteCommand::class);
        $commands->add('swagger bake', Commands\BakeCommand::class);
        $commands->add('swagger models', Commands\ModelCommand::class);
        $commands->add('swagger install', Commands\InstallCommand::class);

        return $commands;
    }
}
