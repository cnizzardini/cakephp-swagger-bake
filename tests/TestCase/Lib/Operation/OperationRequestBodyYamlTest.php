<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagForm;
use SwaggerBake\Lib\Annotation\SwagRequestBody;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Operation\OperationRequestBody;
use SwaggerBake\Lib\Swagger;

class OperationRequestBodyYamlTest extends TestCase
{
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];


    /**
     * @see https://github.com/cnizzardini/cakephp-swagger-bake/issues/274
     */
    public function test_yaml_schema_overwriting_cakephp_model_schema(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['create'],
                'map' => [
                    'testNestedObjectYaml' => [
                        'method' => 'post',
                        'action' => 'testNestedObjectYaml',
                    ]
                ]
            ]);
        });
        $config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-overwrite-model.yml',
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
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = new Swagger($cakeModels);

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:add'];

        $operationRequestBody = new OperationRequestBody(
            $swagger,
            (new Operation())->setHttpMethod('POST'),
            [],
            $route,
            $swagger->getArray()['components']['schemas']['Employee']
        );

        $content = $operationRequestBody
            ->getOperationWithRequestBody()
            ->getRequestBody()
            ->getContentByType('application/x-www-form-urlencoded');

        $this->assertEquals('#/components/schemas/Employee', $content->getSchema());
    }

    /**
     * @see https://github.com/cnizzardini/cakephp-swagger-bake/issues/437
     */
    public function test_nested_object_yaml(): void
    {
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['create', 'testNestedObjectYaml'],
                'map' => [
                    'testNestedObjectYaml' => [
                        'method' => 'post',
                        'action' => 'testNestedObjectYaml',
                    ]
                ]
            ]);
        });
        $config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/openapi-with-nested-objects.yml',
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
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = new Swagger($cakeModels);

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:testnestedobjectyaml'];

        $operationRequestBody = new OperationRequestBody(
            $swagger,
            (new Operation())->setHttpMethod('POST'),
            [],
            $route,
            $swagger->getArray()['components']['schemas']['Place']
        );

        $content = $operationRequestBody
            ->getOperationWithRequestBody()
            ->getRequestBody()
            ->getContentByType('application/x-www-form-urlencoded');

        $this->assertEquals('#/components/schemas/Place', $content->getSchema());
    }
}