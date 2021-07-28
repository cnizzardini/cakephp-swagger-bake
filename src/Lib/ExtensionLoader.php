<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Extension\ExtensionInterface;

class ExtensionLoader
{
    /**
     * @var string[]
     */
    private const EXTENSIONS = [
        '\SwaggerBake\Lib\Extension\CakeSearch\Extension',
    ];

    /**
     * Loads extensions from self::EXTENSIONS
     *
     * @return void
     */
    public static function load(): void
    {
        foreach (self::EXTENSIONS as $extension) {
            $instance = new $extension();

            if (!$instance instanceof ExtensionInterface) {
                // @codeCoverageIgnoreStart
                triggerWarning("$extension must implement ExtensionInterface");
                continue;
                // @codeCoverageIgnoreEnd
            }

            if (!$instance->isSupported()) {
                continue;
            }

            $instance->loadAnnotations();
            $instance->registerListeners();
        }
    }
}
