<?php

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Extension\ExtensionInterface;

class ExtensionLoader
{
    private const EXTENSIONS = [
        '\SwaggerBake\Lib\Extension\CakeSearch\Extension'
    ];

    public static function load() : void
    {
        foreach (SELF::EXTENSIONS as $extension) {

            $instance = new $extension();

            if (!$instance instanceof ExtensionInterface) {
                triggerWarning("$extension must implement ExtensionInterface");
                continue;
            }

            if (!$instance->isSupported()) {
                triggerWarning("$extension failed to load");
                continue;
            }

            $instance->loadAnnotations();
            $instance->registerListeners();
        }
    }
}