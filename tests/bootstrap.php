<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;

define('SWAGGER_BAKE_TEST_ROOT', dirname(__DIR__));
define('TEST', SWAGGER_BAKE_TEST_ROOT . DS . 'tests');
define('SWAGGER_BAKE_TEST_APP', TEST . DS . 'test_app' . DS);
define('ROOT', SWAGGER_BAKE_TEST_APP);
define('APP_DIR', 'test_app');
define('TMP', sys_get_temp_dir() . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', dirname(__DIR__) . DS . 'vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('TEST_APP', SWAGGER_BAKE_TEST_APP);
define('WWW_ROOT', SWAGGER_BAKE_TEST_APP . DS . 'webroot');
define('APP', SWAGGER_BAKE_TEST_APP . 'src' . DS);
define('CONFIG', SWAGGER_BAKE_TEST_APP . 'config' . DS);

ini_set('error_reporting', E_ALL);

$webRoot = SWAGGER_BAKE_TEST_APP . DS . 'webroot';
if (!is_dir($webRoot)) {
    mkdir($webRoot);
}
$swaggerJsonFile = $webRoot . DS . 'swagger.json';
if (!file_exists($swaggerJsonFile)) {
    touch($swaggerJsonFile);
}

require_once CORE_PATH . 'config/bootstrap.php';

Configure::write('debug', true);
/*
 * Create the test application
 * @see tests/test_app
 */
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

/*
 * Set test database and load schema
 * @link https://book.cakephp.org/4/en/development/testing.html#creating-test-database-schema
 */
putenv('DB_DSN=sqlite:///:memory:');
ConnectionManager::setConfig('test', ['url' => getenv('DB_DSN')]);
ConnectionManager::setConfig('test_custom_i18n_datasource', ['url' => getenv('DB_DSN')]);
(new SchemaLoader())->loadInternalFile(__DIR__ . DS . 'schema.php');
