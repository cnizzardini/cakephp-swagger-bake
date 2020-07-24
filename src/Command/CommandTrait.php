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
     * @return void
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException
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
