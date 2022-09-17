<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\Generic;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class MediaTypeTraitTest extends TestCase
{
    public function test_validation_schema_type_throws_exception(): void
    {
        $config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-with-generic-collection.yml',
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

        $cakeRoute = new RouteScanner(new Router(), $config);
        $swagger = (new Swagger(new ModelScanner($cakeRoute, $config), $config))->build();

        $this->expectException(InvalidArgumentException::class);

        (new Generic($swagger))->buildSchema('#/components/schemas/thing', 'nope');
    }
}