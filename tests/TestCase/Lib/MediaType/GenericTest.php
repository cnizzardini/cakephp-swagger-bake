<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\Generic;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class GenericTest extends TestCase
{
    private Router $router;

    private Configuration $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->router = new Router();

        $this->config = new Configuration([
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
    }

    public function test_collection(): void
    {
        $cakeRoute = new RouteScanner($this->router, $this->config);
        $swagger = (new Swagger(new ModelScanner($cakeRoute, $this->config), $this->config))->build();
        $schema = (new Generic($swagger))->buildSchema('#/components/schemas/thing', 'array');
        $this->assertEquals(
            '#/x-swagger-bake/components/schemas/Generic-Collection',
            $schema->getAllOf()[0]['$ref']
        );
        $this->assertArrayHasKey('data', $schema->getProperties());
    }

    public function test_collection_with_associations(): void
    {
        $cakeRoute = new RouteScanner($this->router, $this->config);
        $swagger = (new Swagger(new ModelScanner($cakeRoute, $this->config), $this->config))->build();

        $schema = (new Schema())
            ->setAllOf(['$ref' => $parentRef = '#/components/schemas/City'])
            ->setProperties([
                (new Schema())
                    ->setName('Country')
                    ->setProperties([
                        (new SchemaProperty())->setName('id'),
                        (new SchemaProperty())->setName('name'),
                    ]),
                (new Schema())
                    ->setName('PostalCode')
                    ->setProperties([
                        (new SchemaProperty())->setName('id'),
                        (new SchemaProperty())->setName('PostalCode')
                    ]),
            ]);

        $schema = (new Generic($swagger))->buildSchema($schema, 'array');
        $items = $schema->getProperties()['data']->getItems();
        $this->assertEquals($parentRef, $items['allOf']['$ref']);
        $this->assertArrayHasKey('Country', $items['properties']);
        $this->assertArrayHasKey('PostalCode', $items['properties']);
        $this->assertEquals(
            '#/x-swagger-bake/components/schemas/Generic-Collection',
            $schema->getAllOf()[0]['$ref']
        );
        $this->assertArrayHasKey('data', $schema->getProperties());
    }
}