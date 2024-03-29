<?php

namespace SwaggerBake\Test\TestCase\Lib\Attribute;

use Cake\Routing\Router;
use Cake\Routing\RouteBuilder;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Swagger;

class OpenApiDtoTest extends TestCase
{
    /**
     * @var string[]
     */
    public array $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    private Configuration $config;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/json'],
            'responseContentTypes' => ['application/json'],
            'namespaces' => [
                'controllers' => ['\SwaggerBakeTest\App\\'],
                'entities' => ['\SwaggerBakeTest\App\\'],
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ], SWAGGER_BAKE_TEST_APP);
    }

    /**
     * When an OpenApiDto is used on a http get controller action, the properties are displayed as OpenAPI query
     * parameters.
     */
    public function test_openapi_dto_query(): void
    {
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['dtoQuery'],
                'map' => [
                    'dtoQuery' => [
                        'action' => 'dtoQuery',
                        'method' => 'GET',
                        'path' => 'dto-query'
                    ]
                ]
            ]);
        });

        $cakeRoute = new RouteScanner(new Router(), $this->config);
        $swagger = (new Swagger(new ModelScanner($cakeRoute, $this->config), $this->config))->build();
        $arr = json_decode($swagger->toString(), true);

        $properties = ['lazy', 'first_name', 'last_name', 'title', 'age', 'date',];
        $operation = $arr['paths']['/employees/dto-query']['get'];
        foreach ($properties as $x => $property) {
            $this->assertEquals($property, $operation['parameters'][$x]['name']);
        }
    }

    /**
     * When an OpenApiDto is used on a http post controller action, the properties are displayed as OpenAPI schema
     * properties.
     */
    public function test_openapi_dto_post(): void
    {
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['dtoPost'],
                'map' => [
                    'dtoPost' => [
                        'action' => 'dtoPost',
                        'method' => 'POST',
                        'path' => 'dto-post'
                    ]
                ]
            ]);
        });

        $cakeRoute = new RouteScanner(new Router(), $this->config);
        $swagger = (new Swagger(new ModelScanner($cakeRoute, $this->config), $this->config))->build();
        $arr = json_decode($swagger->toString(), true);

        $names = ['first_name', 'last_name', 'title', 'age', 'date', 'lazy', ];
        $operation = $arr['paths']['/employees/dto-post']['post'];
        $properties = $operation['requestBody']['content']['application/json']['schema']['properties'];
        foreach ($names as $property) {
            $this->assertArrayHasKey($property, $properties);
        }
    }

    /**
     * When a http post controller action has an OpenApiDto that is an instance of a CakePHP Form, the
     * properties are built from the modelless form's schema.
     */
    public function test_openapi_dto_modelless_post_form(): void
    {
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['modellessFormPost'],
                'map' => [
                    'modellessFormPost' => [
                        'action' => 'modellessFormPost',
                        'method' => 'POST',
                        'path' => 'modelless-form-post'
                    ],
                ]
            ]);
        });

        $cakeRoute = new RouteScanner(new Router(), $this->config);
        $swagger = (new Swagger(new ModelScanner($cakeRoute, $this->config), $this->config))->build();
        $arr = json_decode($swagger->toString(), true);
        $operation = $arr['paths']['/employees/modelless-form-post']['post'];
        $properties = $operation['requestBody']['content']['application/json']['schema']['properties'];
        $this->assertEquals([
            'email' => [
                'minLength' => 1,
                'type' => 'string',
            ],
            'name' => [
                'minLength' => 1,
                'maxLength' => 64,
                'type' => 'string',
            ],
            'comments' => [
                'minLength' => 1,
                'type' => 'string',
            ],
        ], $properties);

    }

    /**
     * When a http get controller action has an OpenApiDto that is an instance of a CakePHP Form, the
     * query parameters are built from the modelless form's schema.
     */
    public function test_openapi_dto_modelless_get_form(): void
    {
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees', [
                'only' => ['modellessFormGet'],
                'map' => [
                    'modellessFormGet' => [
                        'action' => 'modellessFormGet',
                        'method' => 'GET',
                        'path' => 'modelless-form-get'
                    ],
                ]
            ]);
        });

        $cakeRoute = new RouteScanner(new Router(), $this->config);
        $swagger = (new Swagger(new ModelScanner($cakeRoute, $this->config), $this->config))->build();
        $arr = json_decode($swagger->toString(), true);
        $operation = $arr['paths']['/employees/modelless-form-get']['get'];
        $this->assertCount(3, $operation['parameters']);
    }
}