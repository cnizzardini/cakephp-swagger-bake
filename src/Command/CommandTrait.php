<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Core\Configure;
use Exception;
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
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException if config file not found
     * @throws \RuntimeException if config var SwaggerBake not found
     */
    public function loadConfig(string $config = 'swagger_bake'): void
    {
        if ($config !== 'swagger_bake') {
            Configure::delete('SwaggerBake');
        }

        // Catch a generic exception here to support older versions of CakePHP which throw
        // \Cake\Core\Exception\Exception instead of \Cake\Core\Exception\CakeException in newer versions
        try {
            Configure::load($config, 'default');
        } catch (Exception $e) {
            throw new SwaggerBakeRunTimeException(
                "SwaggerBake config file `$config` is missing or " . get_class($e) . ' ' . $e->getMessage()
            );
        }

        Configure::readOrFail('SwaggerBake');

        AnnotationLoader::load();
        ExtensionLoader::load();
    }
}
