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
    public array $fixtures = [
        'plugin.SwaggerBake.DepartmentEmployees',
        'plugin.SwaggerBake.Employees',
    ];

    private Configuration $config;

    public function setUp(): void
    {
        parent::setUp();
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $builder) {
            $builder->setExtensions(['json']);
            $builder->resources('Employees');
        });

        $this->config = new Configuration([
            'connectionName' => 'test',
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
        $routeScanner = new RouteScanner(new Router(), $this->config);
        $swagger = (new SwaggerFactory($this->config))->create();

        $schema = (new OperationResponseAssociation(
            $swagger->build(),
            $this->config,
            $routeScanner->getRoutes()['employees:view'],
            null
        ))->build(new OpenApiResponse(
            associations: ['whiteList' => ['DepartmentEmployees']]
        ));

        $schema = (new JsonLd())->buildSchema($schema, 'object');
        $allOf = $schema->getItems()['properties']['department_employees']['items']['allOf'];
        $this->assertNotEmpty($allOf);

        $this->assertNotEmpty(array_filter($allOf, function($item) {
            return isset($item['$ref']) && $item['$ref'] == JsonLd::JSONLD_ITEM;
        }));
        $this->assertNotEmpty(array_filter($allOf, function($item) {
            return isset($item['$ref']) && $item['$ref'] == '#/x-swagger-bake/components/schemas/DepartmentEmployee';
        }));
    }

    /**
     * Employee hasMany DepartmentEmployees
     * DepartmentEmployees hasOne Department
     */
    public function test_item_collection_association(): void
    {
        $routeScanner = new RouteScanner(new Router(), $this->config);
        $swagger = (new SwaggerFactory($this->config))->create();

        $schema = (new OperationResponseAssociation(
            $swagger->build(),
            $this->config,
            $routeScanner->getRoutes()['employees:view'],
            null
        ))->build(new OpenApiResponse(
            schemaType: 'array',
            associations: ['whiteList' => ['DepartmentEmployees']]
        ));

        $schema = (new JsonLd())->buildSchema($schema, 'array');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertEquals(JsonLd::JSONLD_COLLECTION, $schema->getAllOf()[0]['$ref']);
        /** @var SchemaProperty $schemaProperty */
        $schemaProperty = $schema->getProperties()['member'];
        $allOf = $schemaProperty->getItems()['properties']['department_employees']['items']['allOf'];
        $this->assertNotEmpty($allOf);

        $this->assertNotEmpty(array_filter($allOf, function($item) {
            return isset($item['$ref']) && $item['$ref'] == JsonLd::JSONLD_ITEM;
        }));
        $this->assertNotEmpty(array_filter($allOf, function($item) {
            return isset($item['$ref']) && $item['$ref'] == '#/x-swagger-bake/components/schemas/DepartmentEmployee';
        }));
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

        $this->assertObjectHasProperty('test_string', $object->items->properties);
        $this->assertObjectHasProperty('test_ref_entity', $object->items->properties);
        $this->assertCount(2, $object->items->properties->test_ref_entity->allOf);
        $this->assertObjectHasProperty('test_object', $object->items->properties);
    }
}
