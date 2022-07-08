<?php

namespace SwaggerBake\Test\TestCase\Lib\Route;

use Cake\Collection\Collection;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class PrefixRouteTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.DepartmentEmployees',
        'plugin.SwaggerBake.Departments',
        'plugin.SwaggerBake.Employees',
    ];

    /**
     * @var Router
     */
    private $router;

    /**
     * @var array
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Departments');
            $builder->resources('Departments', [
                'prefix' => 'Admin',
                'path' => 'admin/departments',
                'only' => ['index']
            ]);
        });
        $this->router = $router;

        $this->config = [
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/x-www-form-urlencoded'],
            'responseContentTypes' => ['application/json'],
            'namespaces' => [
                'controllers' => ['\SwaggerBakeTest\App\\'],
                'entities' => ['\SwaggerBakeTest\App\\'],
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ];
    }

    /**
     * Tests prefix routing by verifying that paths are added for:
     * - App\Controller\DepartmentsController
     * - App\Controller\Admin\DepartmentsController
     */
    public function test_prefix_routing_with_same_controller_shortname(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);
        $openApi = (new Swagger(new ModelScanner($cakeRoute, $config)))->getArray();
        $paths = new Collection(array_keys($openApi['paths']));
        $this->assertTrue($paths->contains('/admin/departments'));
        $this->assertTrue($paths->contains('/departments'));
    }

    public function test_controller_name_lowercase(): void
    {
        $this->router::scope('/', function (RouteBuilder $builder) {
            $builder->get('/non-standard', ['controller' => 'operations', 'action' => 'index']);
        });
        $scanner = new RouteScanner($this->router, new Configuration($this->config, SWAGGER_BAKE_TEST_APP));
        $routes = $scanner->getRoutes();
        $this->assertArrayHasKey('operations:index', $routes);
        $this->assertNotEmpty(
            $routes['operations:index']->getControllerFqn(),
            'Controller fqn has not been set for lowercase controller name'
        );
    }
}