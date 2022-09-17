<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationRequestBody;
use SwaggerBake\Lib\Swagger;

class OperationRequestBodyYamlTest extends TestCase
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
                'only' => ['create']
            ]);
        });
        $this->router = $router;

        $this->config = [
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
        ];
    }

    /**
     * @link https://github.com/cnizzardini/cakephp-swagger-bake/issues/274
     */
    public function test_yaml_schema_overwriting_cakephp_model_schema(): void
    {
        $config = new Configuration($this->config, SWAGGER_BAKE_TEST_APP);
        $cakeRoute = new RouteScanner($this->router, $config);
        $cakeModels = new ModelScanner($cakeRoute, $config);
        $swagger = (new Swagger($cakeModels, $config))->build();

        $routes = $cakeRoute->getRoutes();
        $route = $routes['employees:add'];

        $operationRequestBody = new OperationRequestBody(
            $swagger,
            new Operation('hello', 'post'),
            $route,
            null,
            $swagger->getArray()['components']['schemas']['Employee']
        );

        $content = $operationRequestBody
            ->getOperationWithRequestBody()
            ->getRequestBody()
            ->getContentByType('application/x-www-form-urlencoded');

        $this->assertEquals('#/components/schemas/Employee', $content->getSchema()->getRefPath());
    }
}