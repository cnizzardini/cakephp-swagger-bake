<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\Operation\OperationResponse;
use SwaggerBake\Lib\OpenApi\Schema;

class OperationResponseTest extends TestCase
{
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var array
     */
    private $routes;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $router = new Router();
        $router::scope('/api', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => [
                    'index',
                    'create',
                    'delete',
                    'noResponsesDefined',
                    'textPlain'
                ],
                'map' => [
                    'noResponsesDefined'  => [
                        'method' => 'get',
                        'action' => 'noResponseDefined',
                        'path' => 'no-responses-defined'
                    ],
                    'textPlain'  => [
                        'method' => 'get',
                        'action' => 'textPlain',
                        'path' => 'text-plain'
                    ],
                ]
            ]);
        });
        $this->router = $router;

        if (!$this->config instanceof Configuration) {
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
        }

        if (empty($this->routes)) {
            $cakeRoute = new RouteScanner($this->router, $this->config);
            $this->routes = $cakeRoute->getRoutes();
        }
    }

    public function testGetOperationWithAnnotatedResponse()
    {
        $route = $this->routes['employees:index'];

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/** @throws Exception */'),
            [
                new SwagResponseSchema([
                    'httpCode' => 200,
                ]),
            ],
            $route,
            null
        );

        $operation = $operationResponse->getOperationWithResponses();

        $this->assertInstanceOf(Response::class, $operation->getResponseByCode(200));
        $this->assertInstanceOf(Response::class, $operation->getResponseByCode(500));
    }

    public function testGetOperationWithSchemaResponse()
    {
        $route = $this->routes['employees:add'];

        $schema = (new Schema())
            ->setName('Employee')
            ->setType('object')
        ;

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/**  */'),
            [],
            $route,
            $schema
        );

        $operation = $operationResponse->getOperationWithResponses();

        $this->assertInstanceOf(Response::class, $operation->getResponseByCode(200));
    }

    public function testAddOperationWithNoResponseDefined()
    {
        $route = $this->routes['employees:add'];

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/**  */'),
            [],
            $route,
            null
        );

        $operation = $operationResponse->getOperationWithResponses();
        $response = $operation->getResponseByCode(200);
        $this->assertNotEmpty($response);

        $content = $response->getContentByMimeType('application/json');

        $this->assertNotEmpty($content);
        $this->assertNotEmpty($content->getSchema());
    }

    public function testDeleteActionResponseWithHttp204()
    {
        $route = $this->routes['employees:delete'];

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/**  */'),
            [],
            $route,
            null
        );

        $operation = $operationResponse->getOperationWithResponses();
        $this->assertNotEmpty($operation->getResponseByCode(204));
    }

    public function testNoResponseDefined()
    {
        $route = $this->routes['employees:noresponsedefined'];

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/**  */'),
            [],
            $route,
            null
        );

        $operation = $operationResponse->getOperationWithResponses();
        $response = $operation->getResponseByCode(200);
        $this->assertNotEmpty($response);

        $content = $response->getContentByMimeType('application/json');
        $this->assertNotEmpty($content);
        $this->assertNotEmpty($content->getSchema());
    }

    public function testGetOperationWithSwagResponseSchemaRefEntity()
    {
        $route = $this->routes['employees:index'];

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/** */'),
            [
                new SwagResponseSchema([
                    'refEntity' => '#/components/schema/Employee',
                ]),
            ],
            $route,
            null
        );

        $operation = $operationResponse->getOperationWithResponses();

        $content = $operation->getResponseByCode(200)->getContentByMimeType('application/json');

        $this->assertEquals('#/components/schema/Employee', $content->getSchema()->getRefEntity());
    }

    public function testGetOperationWithSwagResponseSchemaItems()
    {
        $route = $this->routes['employees:index'];

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/** */'),
            [
                new SwagResponseSchema([
                    'schemaItems' => [ '$ref' => '#/components/schema/Employee']
                ]),
            ],
            $route,
            null
        );

        $operation = $operationResponse->getOperationWithResponses();

        $content = $operation->getResponseByCode(200)->getContentByMimeType('application/json');


        $this->assertEquals('array', $content->getSchema()->getType());
        $this->assertArrayHasKey('$ref', $content->getSchema()->getItems());
        $this->assertEquals('#/components/schema/Employee', $content->getSchema()->getItems()['$ref']);
    }

    public function testGetOperationWithSwagResponseSchemaTextPlain()
    {
        $route = $this->routes['employees:textplain'];

        $operationResponse = new OperationResponse(
            $this->config,
            new Operation(),
            DocBlockFactory::createInstance()->create('/** */'),
            [
                new SwagResponseSchema([
                    'mimeTypes' => ['text/plain'],
                    'schemaFormat' => 'date-time'
                ]),
            ],
            $route,
            null
        );

        $operation = $operationResponse->getOperationWithResponses();

        $content = $operation->getResponseByCode(200)->getContentByMimeType('text/plain');

        $this->assertEquals('string', $content->getSchema()->getType());
        $this->assertEquals('date-time', $content->getSchema()->getFormat());
    }
}