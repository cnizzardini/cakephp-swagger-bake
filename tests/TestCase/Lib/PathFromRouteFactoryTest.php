<?php

namespace SwaggerBake\Test\TestCase\Lib;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
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
}