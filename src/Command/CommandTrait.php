<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Core\Configure;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\ExtensionLoader;

trait CommandTrait
{
    /**
     * Loads configuration
     *
     * @param string $config your applications swagger_bake config
     * @return void
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
     */
    public function loadConfig(string $config = 'swagger_bake'): void
    {
        if ($config !== 'swagger_bake') {
            Configure::delete('SwaggerBake');
        }

        if (!Configure::load($config, 'default')) {
            throw new SwaggerBakeRunTimeException(
                "SwaggerBake configuration file `$config` is missing"
            );
        }

        AnnotationLoader::load();
        ExtensionLoader::load();
    }
}
