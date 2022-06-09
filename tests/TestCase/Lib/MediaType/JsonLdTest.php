<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Collection\Collection;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Attribute\OpenApiResponse;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\SwaggerFactory;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\MediaType\JsonLd;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
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

    private Router $router;

    private Configuration $config;

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
        $routes = $cakeRoute->getRoutes();

        $schema = (new OperationResponseAssociation(
            (new SwaggerFactory($this->config, new RouteScanner($this->router, $this->config)))->create(),
            $routes['employees:view'],
            null
        ))->build(new OpenApiResponse(
            associations: ['whiteList' => ['DepartmentEmployees']]
        ));

        $schema = (new JsonLd())->buildSchema($schema, 'object');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertTrue(isset($object->items->properties->department_employees->items->allOf));
        $allOf = $object->items->properties->department_employees->items->allOf;
        $this->assertNotEmpty(
            (new Collection($allOf))->filter(function($item) {
                return isset($item['$ref']) && $item['$ref'] == JsonLd::JSONLD_ITEM;
            })
        );
        $this->assertNotEmpty(
            (new Collection($allOf))->filter(function($item) {
                return isset($item['$ref']) && $item['$ref'] == '#/x-swagger-bake/components/schemas/DepartmentEmployee';
            })
        );
    }

    /**
     * Employee hasMany DepartmentEmployees
     * DepartmentEmployees hasOne Department
     */
    public function test_item_collection_association(): void
    {
        $cakeRoute = new RouteScanner($this->router, $this->config);
        $routes = $cakeRoute->getRoutes();

        $schema = (new OperationResponseAssociation(
            (new SwaggerFactory($this->config, new RouteScanner($this->router, $this->config)))->create(),
            $routes['employees:view'],
            null
        ))->build(new OpenApiResponse(
            schemaType: 'array',
            associations: ['whiteList' => ['DepartmentEmployees']]
        ));

        $schema = (new JsonLd())->buildSchema($schema, 'array');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertEquals(JsonLd::JSONLD_COLLECTION, $object->allOf[0]->{'$ref'});
        $this->assertTrue(isset($object->properties->member->items->properties->department_employees->items->allOf));

        $allOf = $object->properties->member->items->properties->department_employees->items->allOf;
        $this->assertNotEmpty(
            (new Collection($allOf))->filter(function($item) {
                return isset($item['$ref']) && $item['$ref'] == JsonLd::JSONLD_ITEM;
            })
        );
        $this->assertNotEmpty(
            (new Collection($allOf))->filter(function($item) {
                return isset($item['$ref']) && $item['$ref'] == '#/x-swagger-bake/components/schemas/DepartmentEmployee';
            })
        );
    }

    public function test_nested_associations(): void
    {
        $schema = new Schema();
        $schema->setProperties([
            (new SchemaProperty())
                ->setType('string')
                ->setName('test_string'),
            (new SchemaProperty())
                ->setType('object')
                ->setName('test_ref_entity')
                ->setRefEntity('#/components/schemas/TestEntity'),
            (new Schema())
                ->setType('object')
                ->setName('test_object')
                ->setProperties([
                    (new SchemaProperty())
                        ->setType('string')->setName('test_string')
                ])
        ]);

        $schema = (new JsonLd())->buildSchema($schema, 'object');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertObjectHasAttribute('test_string', $object->items->properties);
        $this->assertObjectHasAttribute('test_ref_entity', $object->items->properties);
        $this->assertCount(2, $object->items->properties->test_ref_entity->allOf);
        $this->assertObjectHasAttribute('test_object', $object->items->properties);
    }
}