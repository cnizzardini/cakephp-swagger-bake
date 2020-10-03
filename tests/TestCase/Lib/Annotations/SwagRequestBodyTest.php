<?php


namespace SwaggerBake\Test\TestCase\Lib\Annotations;

use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;

class SwagRequestBodyTest extends TestCase
{
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private $router;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/api', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'map' => [
                    'customPost' => [
                        'action' => 'customPost',
                        'method' => 'POST',
                        'path' => 'custom-post'
                    ],
                ]
            ]);
        });
        $this->router = $router;

        $this->config = new Configuration([
            'prefix' => '/api',
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
        ], SWAGGER_BAKE_TEST_APP);

        AnnotationLoader::load();
    }

    public function testSwagRequestBody()
    {
        $routeScanner = new RouteScanner($this->router, $this->config);

        $swagger = new Swagger(new ModelScanner($routeScanner, $this->config));
        $arr = json_decode($swagger->toString(), true);

        $operation = $arr['paths']['/employees/custom-post']['post'];
        $body = $operation['requestBody'];

        $this->assertEquals('Hello', $body['description']);
        $this->assertTrue($body['required']);
        $this->assertCount(1, $body['content']['application/x-www-form-urlencoded']['schema']['properties']);
    }

}