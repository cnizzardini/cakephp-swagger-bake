<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\ExtensionLoader;

trait CommandTrait
{
    /**
     * Loads configuration
     *
     * @param \Cake\Console\Arguments $args Cli Arguments instance
     * @return void
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException if config file not found
     * @throws \RuntimeException if config var SwaggerBake not found
     */
    public function loadConfig(Arguments $args): void
    {
        $config = (string)$args->getOption('config');
        if ($config !== 'swagger_bake') {
            Configure::delete('SwaggerBake');
        }

        try {
            Configure::load($config, 'default');
        } catch (CakeException $e) {
            throw new SwaggerBakeRunTimeException(
                "SwaggerBake config file `$config` is missing or " . get_class($e) . ' ' . $e->getMessage()
            );
        }

        Configure::readOrFail('SwaggerBake');

        ExtensionLoader::load();
    }
}
