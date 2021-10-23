<?php

namespace SwaggerBake\Test\TestCase\Lib\Attribute;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use SwaggerBake\Lib\Attribute\AbstractOpenApiParameter;
use SwaggerBake\Lib\Attribute\OpenApiPathParam;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class OpenApiPathParamTest extends TestCase
{
    public function test(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('OperationPath', [
                'only' => ['pathParameter'],
                'map' => [
                    'pathParameter' => [
                        'action' => 'pathParameter',
                        'method' => 'GET',
                        'path' => 'path-parameter/:id'
                    ],
                ]
            ]);
        });

        $config = new Configuration([
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
        ], SWAGGER_BAKE_TEST_APP);

        $cakeRoute = new RouteScanner($router, $config);

        $swagger = new Swagger(new ModelScanner($cakeRoute, $config));
        $arr = json_decode($swagger->toString(), true);

        $params = $arr['paths']['/operation-path/path-parameter/{id}']['get']['parameters'];
        $param = reset($params);

        $this->assertEquals('integer', $param['schema']['type']);
        $this->assertEquals('ID', $param['description']);
        $this->assertEquals('int64', $param['schema']['format']);
    }

    /**
     * @see AbstractOpenApiParameter
     */
    public function test_constructor_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenApiPathParam();
    }
}