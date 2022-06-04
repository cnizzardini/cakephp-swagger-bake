<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

class ExtensionLoader
{
    /**
     * Returns a list of Extensions
     *
     * @return object[]
     */
    private static function extensions(): array
    {
        return [
            \SwaggerBake\Lib\Extension\CakeSearch\Extension::create(),
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
