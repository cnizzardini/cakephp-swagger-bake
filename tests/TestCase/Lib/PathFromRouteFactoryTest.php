<?php

namespace SwaggerBake\Test\TestCase\Lib;

use Cake\Routing\Route\Route;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\PathFromRouteFactory;

class PathFromRouteFactoryTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private Router $router;

    private array $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => 'index'
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

    public function test(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);

        $routes = $cakeRoute->getRoutes();
        $route = reset($routes);

        $path = (new PathFromRouteFactory($route))->create();
        $this->assertInstanceOf(Path::class, $path);
        $this->assertEquals('/employees', $path->getResource());
    }

    public function test_create_returns_null_on_empty_http_methods(): void
    {
        $decorator = new RouteDecorator(new Route('/template', []));
        $decorator->setControllerFqn('\App\Controller\EmployeesController');
        $path = new PathFromRouteFactory($decorator);
        $this->assertNull($path->create());
    }

    public function test_create_returns_null_when_controller_does_not_exist(): void
    {
        // null controller should return null
        $decorator = new RouteDecorator(new Route('/template', ['_method' => 'GET']));
        $path = new PathFromRouteFactory($decorator);
        $this->assertNull($path->create());

        // class does not exist should return null
        $decorator = new RouteDecorator(new Route('/template', ['_method' => 'GET']));
        $decorator->setControllerFqn('\App\Controller\Nope');
        $path = new PathFromRouteFactory($decorator);
        $this->assertNull($path->create());
    }
}