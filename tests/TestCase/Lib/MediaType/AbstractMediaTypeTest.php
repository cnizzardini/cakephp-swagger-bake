<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class AbstractMediaTypeTest extends TestCase
{
    public function test_construct_throws_invalid_argument_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-with-existing.yml',
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
        $routeScanner = new RouteScanner(new Router(), $config);
        $swagger = new Swagger(new ModelScanner($routeScanner, $config));

        new HalJson([], $swagger);
    }
}