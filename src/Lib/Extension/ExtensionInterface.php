<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Extension;

/**
 * Interface ExtensionInterface
 *
 * @package SwaggerBake\Lib\Extension
 */
interface ExtensionInterface
{
    /**
     * Whether this extension can be supported. For instance, if the extension requires a plugin such as Search, then
     * you would check if that plugin is loaded and return a boolean result
     *
     * @return bool
     */
    public function isSupported(): bool;

    /**
     * This will register the listener
     *
     * @see https://book.cakephp.org/4/en/core-libraries/events.html#registering-anonymous-listeners
     * @return void
     */
    public function registerListeners(): void;
}
