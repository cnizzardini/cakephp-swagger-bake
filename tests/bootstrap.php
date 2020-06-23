<?php
declare(strict_types=1);

use Cake\Cache\Cache;
use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;
use Cake\Utility\Security;

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

if (is_file('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('SWAGGER_BAKE_TEST_ROOT', dirname(__DIR__));
define('SWAGGER_BAKE_TEST_APP', SWAGGER_BAKE_TEST_ROOT . DS . 'tests' . DS . 'test_app');

define('ROOT', SWAGGER_BAKE_TEST_APP);
define('APP_DIR', 'test_app');

define('TMP', sys_get_temp_dir() . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

define('CAKE_CORE_INCLUDE_PATH', dirname(__DIR__) . DS . 'vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('CORE_TESTS', CORE_PATH . 'tests' . DS);
define('CORE_TEST_CASES', CORE_TESTS . 'TestCase');
define('TEST_APP', SWAGGER_BAKE_TEST_APP);

define('WWW_ROOT', SWAGGER_BAKE_TEST_APP . DS . 'webroot');
define('APP', SWAGGER_BAKE_TEST_APP . DS . 'src');
define('CONFIG', SWAGGER_BAKE_TEST_APP . DS . 'config' . DS);

// phpcs:disable
@mkdir(LOGS);
@mkdir(SESSIONS);
@mkdir(CACHE);
@mkdir(CACHE . 'views');
@mkdir(CACHE . 'models');
// phpcs:enable

ini_set('error_reporting', 'E_ALL ^ E_DEPRECATED');

$webRoot = SWAGGER_BAKE_TEST_APP . DS . 'webroot';
if (!file_exists($webRoot)) {
    mkdir($webRoot);
}
$swaggerJsonFile = $webRoot . DS . 'swagger.json';
if (!file_exists($swaggerJsonFile)) {
    touch($swaggerJsonFile);
}

require_once CORE_PATH . 'config/bootstrap.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'SwaggerBakeTest\App',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => APP_DIR,
    'webroot' => 'webroot',
    'wwwRoot' => WWW_ROOT,
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'plugins' => [TEST_APP . 'Plugin' . DS],
        'templates' => [TEST_APP . 'templates' . DS],
        'locales' => [TEST_APP . 'resources' . DS . 'locales' . DS],
    ],
]);

Cache::setConfig([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true,
    ],
    '_cake_model_' => [
        'engine' => 'File',
        'prefix' => 'cake_model_',
        'serialize' => true,
    ],
]);

// Ensure default test connection is defined
if (!getenv('DB_DSN')) {
    putenv('DB_DSN=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', ['url' => getenv('DB_DSN')]);
ConnectionManager::setConfig('test_custom_i18n_datasource', ['url' => getenv('DB_DSN')]);

Configure::write('Session', [
    'defaults' => 'php',
]);

Log::setConfig([
    // 'queries' => [
    //     'className' => 'Console',
    //     'stream' => 'php://stderr',
    //     'scopes' => ['queriesLog']
    // ],
    'debug' => [
        'engine' => 'Cake\Log\Engine\FileLog',
        'levels' => ['notice', 'info', 'debug'],
        'file' => 'debug',
        'path' => LOGS,
    ],
    'error' => [
        'engine' => 'Cake\Log\Engine\FileLog',
        'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        'file' => 'error',
        'path' => LOGS,
    ],
]);

Chronos::setTestNow(Chronos::now());
Security::setSalt('a-long-but-not-random-value');

ini_set('intl.default_locale', 'en_US');
ini_set('session.gc_divisor', '1');

// Fixate sessionid early on, as php7.2+
// does not allow the sessionid to be set after stdout
// has been written to.
session_id('cli');

/**
 * Define fallback values for required constants and configuration.
 * To customize constants and configuration remove this require
 * and define the data required by your plugin here.
 */
//require_once $root . '/vendor/cakephp/cakephp/tests/bootstrap.php';