<?php
declare(strict_types=1);

namespace SwaggerBake;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Command as Commands;

/**
 * Class Plugin
 * @package SwaggerBake
 */
class Plugin extends BasePlugin
{
    public function bootstrap(PluginApplicationInterface $app) : void
    {
        parent::bootstrap($app);
        Configure::load('swagger_bake', 'default');
        AnnotationLoader::load();
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('swagger routes', Commands\RouteCommand::class);
        $commands->add('swagger bake', Commands\BakeCommand::class);
        $commands->add('swagger models', Commands\ModelCommand::class);

        return $commands;
    }
}