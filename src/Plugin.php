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
 * @package SwaggerBake
 */
class Plugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app) : void
    {
        parent::bootstrap($app);
        if (!file_exists(CONFIG . 'swagger_bake.php')) {
            triggerWarning('Missing configuration file for config/swagger_bake.php');
            return;
        }
        Configure::load('swagger_bake', 'default');
        AnnotationLoader::load();
        ExtensionLoader::load();
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('swagger routes', Commands\RouteCommand::class);
        $commands->add('swagger bake', Commands\BakeCommand::class);
        $commands->add('swagger models', Commands\ModelCommand::class);
        $commands->add('swagger install', Commands\InstallCommand::class);

        return $commands;
    }
}