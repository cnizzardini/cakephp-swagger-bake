<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\MediaType\JsonLd;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Operation\OperationResponseAssociation;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class JsonLdTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.DepartmentEmployees',
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

    public function setUp(): void
    {
        parent::setUp();
        $router = new Router();
        $router::scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees');
        });
        $this->router = $router;

        $this->config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/x-www-form-urlencoded'],
            'responseContentTypes' => ['application/ld+json'],
            'namespaces' => [
                'controllers' => ['\SwaggerBakeTest\App\\'],
                'entities' => ['\SwaggerBakeTest\App\\'],
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ], SWAGGER_BAKE_TEST_APP);
    }

    /**
     * Employee hasMany DepartmentEmployees
     * DepartmentEmployees hasOne Department
     */
    public function test_item_with_association(): void
    {
        $cakeRoute = new RouteScanner($this->router, $this->config);
        $swagger = new Swagger(new ModelScanner($cakeRoute, $this->config));
        $routes = $cakeRoute->getRoutes();

        $schema = (new OperationResponseAssociation(
            (new SwaggerFactory($this->config, new RouteScanner($this->router, $this->config)))->create(),
            $routes['employees:view'],
            null
        ))->build(new SwagResponseSchema([
            'schemaType' => 'object',
            'associations' => ['depth' => 1, 'whiteList' => ['DepartmentEmployees']]
        ]));

        $schema = (new JsonLd())->buildSchema($schema, 'object');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertTrue(isset($object->items->properties->department_employees->items->allOf));

        $this->assertEquals(
            JsonLd::JSONLD_ITEM,
            $object->items->properties->department_employees->items->allOf[0]->{'$ref'}
        );

        $this->assertEquals(
            '#/x-swagger-bake/components/schemas/DepartmentEmployee-Read',
            $object->items->properties->department_employees->items->allOf[1]->{'$ref'}
        );
    }

    /**
     * Employee hasMany DepartmentEmployees
     * DepartmentEmployees hasOne Department
     */
    public function test_item_collection_association(): void
    {
        $cakeRoute = new RouteScanner($this->router, $this->config);
        $swagger = new Swagger(new ModelScanner($cakeRoute, $this->config));
        $routes = $cakeRoute->getRoutes();

        $schema = (new OperationResponseAssociation(
            (new SwaggerFactory($this->config, new RouteScanner($this->router, $this->config)))->create(),
            $routes['employees:view'],
            null
        ))->build(new SwagResponseSchema([
            'schemaType' => 'array',
            'associations' => ['depth' => 1, 'whiteList' => ['DepartmentEmployees']]
        ]));

        $schema = (new JsonLd())->buildSchema($schema, 'array');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertEquals(JsonLd::JSONLD_COLLECTION, $object->allOf[0]->{'$ref'});

        $this->assertTrue(isset($object->properties->member->items->properties->department_employees->items->allOf));

        $this->assertEquals(
            JsonLd::JSONLD_ITEM,
            $object->properties->member->items->properties->department_employees->items->allOf[0]->{'$ref'}
        );

        $this->assertEquals(
            '#/x-swagger-bake/components/schemas/DepartmentEmployee-Read',
            $object->properties->member->items->properties->department_employees->items->allOf[1]->{'$ref'}
        );
    }
}