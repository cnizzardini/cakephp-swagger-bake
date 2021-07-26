<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\MediaType\AbstractMediaType;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\Model\ModelScanner;
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

        $schema = (new HalJson($schema, $swagger))->buildSchema('object');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertTrue(isset($object->items->properties->_embedded->items->allOf));

        $this->assertEquals(
            HalJson::HAL_ITEM,
            $object->items->properties->_embedded->items->allOf[0]->{'$ref'}
        );

        $this->assertEquals(
            '#/x-swagger-bake/components/schemas/DepartmentEmployee-Read',
            $object->items->properties->_embedded->items->allOf[1]->{'$ref'}
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

        $schema = (new HalJson($schema, $swagger))->buildSchema('array');
        $object = json_decode(json_encode($schema->jsonSerialize()));

        $this->assertEquals(HalJson::HAL_COLLECTION, $object->allOf[0]->{'$ref'});

        $this->assertTrue(isset($object->properties->_embedded->items->properties->_embedded->items->allOf));

        $allOf = $object->properties->_embedded->items->properties->_embedded->items->allOf;

        $this->assertEquals(HalJson::HAL_ITEM, $allOf[0]->{'$ref'});
        $this->assertEquals('#/x-swagger-bake/components/schemas/DepartmentEmployee-Read', $allOf[1]->{'$ref'});
    }
}