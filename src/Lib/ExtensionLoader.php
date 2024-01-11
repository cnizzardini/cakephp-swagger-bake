<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Extension\CakeSearch\Extension;

class ExtensionLoader
{
    /**
     * Returns a list of Extensions
     *
     * @return array<object>
     */
    private static function extensions(): array
    {
        return [
            Extension::create(),
        ];
    }

    /**
     * Loads extensions from self::EXTENSIONS
     *
     * @return void
     */
    public static function load(): void
    {
        foreach (self::extensions() as $extension) {
            /** @var \SwaggerBake\Lib\Extension\ExtensionInterface $extension */
            $extension->registerListeners();
        }
    }
}
