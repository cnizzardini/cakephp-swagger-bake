<?php

namespace SwaggerBake\Test\TestCase\Lib\Attribute;

use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;

class OpenApiPathTest extends TestCase
{
    /** @var string[]  */
    public array $fixtures = [
        'plugin.SwaggerBake.Employees',
        'plugin.SwaggerBake.EmployeeTitles',
    ];

    private array $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', function (RouteBuilder $routes) {
                $routes->resources('EmployeeTitles');
            });
        });

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

    public function test_summary_and_description(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner(new Router(), $config);

        $swagger = (new Swagger(new ModelScanner($cakeRoute, $config), $config))->build();

        $arr = json_decode($swagger->toString(), true);
        $employees = $arr['paths']['/employees'];

        $this->assertEquals('summary here', $employees['summary']);
        $this->assertEquals('description here', $employees['description']);
    }

    public function test_invisible(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner(new Router(), $config);

        $swagger = (new Swagger(new ModelScanner($cakeRoute, $config), $config))->build();

        $arr = json_decode($swagger->toString(), true);

        $this->assertArrayNotHasKey('/employee-titles', $arr['paths']);
    }
}