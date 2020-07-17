<?php

namespace SwaggerBake\Command;

use Cake\Core\Configure;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\ExtensionLoader;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

trait CommandTrait
{
    /**
     * Loads configuration
     *
     * @return void
     * @throws SwaggerBakeRunTimeException
     */
    public function loadConfig(): void
    {
        if (!file_exists(CONFIG . 'swagger_bake.php')) {
            throw new SwaggerBakeRunTimeException(
                'SwaggerBake configuration file `config/swagger_bake.php` is missing'
            );
        }

        Configure::load('swagger_bake', 'default');
        AnnotationLoader::load();
        ExtensionLoader::load();
    }
}