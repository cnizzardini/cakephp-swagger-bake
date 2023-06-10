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
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Operation\OperationResponseAssociation;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class HalJsonTest extends TestCase
{
    /**
     * @var string[]
     */
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
        'plugin.SwaggerBake.DepartmentEmployees',
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
            'prefix' => '/',
            'yml' => '/config/swagger-bare-bones.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
            'hotReload' => false,
            'exceptionSchema' => 'Exception',
            'requestAccepts' => ['application/x-www-form-urlencoded'],
            'responseContentTypes' => ['application/hal+json'],
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
        $swagger = (new SwaggerFactory($this->config, $routeScanner))->create();
        echo '<pre>' . __FILE__ . ':' . __LINE__;
        print_r($swagger->getArray()['paths']);
        echo '</pre>';
        die();

        $schema = (new OperationResponseAssociation(
            $swagger,
            $routeScanner->getRoutes()['employees:view'],
            null
        ))->build(new OpenApiResponse(
            associations: ['whiteList' => ['DepartmentEmployees']]
        ));

        echo '<pre>' . __FILE__ . ':' . __LINE__;
        print_r($schema);
        echo '</pre>';
        die();


        $schema = (new HalJson())->buildSchema($schema, 'object');
        $data = $schema->getItems()['properties']['_embedded']['items']['allOf'];

        $this->assertNotEmpty(array_filter($data, function ($item) {
            return isset($item['$ref']) && $item['$ref'] == HalJson::HAL_ITEM;
        }));
        $this->assertNotEmpty(array_filter($data, function ($item) {
            return isset($item['$ref']) && $item['$ref'] == '#/x-swagger-bake/components/schemas/DepartmentEmployee';
        }));
    }

    /**
     * Employee hasMany DepartmentEmployees
     * DepartmentEmployees hasOne Department
     */
    public function test_item_collection_association(): void
    {
        $cakeRoute = new RouteScanner(new Router(), $this->config);
        $routes = $cakeRoute->getRoutes();

        $schema = (new OperationResponseAssociation(
            (new SwaggerFactory($this->config, new RouteScanner(new Router(), $this->config)))->create(),
            $routes['employees:view'],
            null
        ))->build(new OpenApiResponse(
            schemaType: 'array',
            associations: ['whiteList' => ['DepartmentEmployees']]
        ));

        $schema = (new HalJson())->buildSchema($schema, 'array');

        $this->assertEquals(HalJson::HAL_COLLECTION, $schema->getAllOf()[0]['$ref']);

        /** @var SchemaProperty $schemaProperty */
        $schemaProperty = $schema->getProperties()['_embedded'];
        $allOf = $schemaProperty->getItems()['properties']['_embedded']['items']['allOf'];

        $this->assertNotEmpty(array_filter($allOf, function ($item) {
            return isset($item['$ref']) && $item['$ref'] == HalJson::HAL_ITEM;
        }));
        $this->assertNotEmpty(array_filter($allOf, function ($item) {
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

        $schema = (new HalJson())->buildSchema($schema, 'object');
        //$object = json_decode(json_encode($schema->jsonSerialize()));
        $properties = $schema->getItems()['properties'];


        //$this->assertObjectHasAttribute('test_string', $object->items->properties);
        //$this->assertObjectHasAttribute('test_ref_entity', $object->items->properties->_embedded->properties);
        $this->assertCount(2, $properties['_embedded']['properties']['test_ref_entity']['allOf']);
        //$this->assertObjectHasAttribute('test_object', $object->items->properties->_embedded->properties);
    }
}