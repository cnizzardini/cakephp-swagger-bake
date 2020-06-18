<?php
declare(strict_types=1);

/**
 * Test suite bootstrap for SwaggerBake.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */
$findRoot = function ($root) {
    do {
        $lastRoot = $root;
        $root = dirname($root);
        if (is_dir($root . '/vendor/cakephp/cakephp')) {
            return $root;
        }
    } while ($root !== $lastRoot);

    throw new Exception("Cannot find the root of the application, unable to run tests");
};
$root = $findRoot(__FILE__);
unset($findRoot);

chdir($root);

require_once $root . '/vendor/autoload.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

//define('ROOT', dirname(__DIR__));
define('APP_DIR', 'test_app');

define('TMP', sys_get_temp_dir() . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

define('SWAGGER_BAKE_TEST_ROOT', dirname(__DIR__));
define('SWAGGER_BAKE_TEST_APP', SWAGGER_BAKE_TEST_ROOT . DS . 'tests' . DS . 'test_app');
define('CONFIG', SWAGGER_BAKE_TEST_APP . DS . 'config' . DS);
define('WWW_ROOT', SWAGGER_BAKE_TEST_APP . DS . 'webroot');
define('APP', SWAGGER_BAKE_TEST_APP);

ini_set('error_reporting', 'E_ALL ^ E_DEPRECATED');

/**
 * Define fallback values for required constants and configuration.
 * To customize constants and configuration remove this require
 * and define the data required by your plugin here.
 */
require_once $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';

if (file_exists($root . '/config/bootstrap.php')) {
    require $root . '/config/bootstrap.php';

    return;
}
