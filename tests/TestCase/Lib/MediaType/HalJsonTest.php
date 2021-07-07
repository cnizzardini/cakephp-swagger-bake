<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class HalJsonTest extends TestCase
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

        AnnotationLoader::load();
    }

    public function test_collection(): void
    {
        $cakeRoute = new RouteScanner($this->router, $this->config);
        $swagger = new Swagger(new ModelScanner($cakeRoute, $this->config));

        /** @var \SwaggerBake\Lib\OpenApi\Path $path */
        $path = $swagger->getArray()['paths']['/employees'];
        $content = $path->getOperations()['get']->getResponses()['200']->getContent()['application/hal+json'];
        $schema = $content->getSchema();

        $this->assertEquals(HalJson::HAL_COLLECTION, $schema->getAllOf()[0]['$ref']);
        $this->assertEquals(
            HalJson::HAL_ITEM,
            $schema->getProperties()['_embedded']->getItems()['allOf'][0]['$ref']
        );
        $this->assertEquals(
            Schema::SCHEMA . 'Employee-Read',
            $schema->getProperties()['_embedded']->getItems()['allOf'][1]['$ref']
        );
    }

    public function test_item(): void
    {
        $cakeRoute = new RouteScanner($this->router, $this->config);
        $swagger = new Swagger(new ModelScanner($cakeRoute, $this->config));

        /** @var \SwaggerBake\Lib\OpenApi\Path $path */
        $path = $swagger->getArray()['paths']['/employees/{id}'];
        $content = $path->getOperations()['get']->getResponses()['200']->getContent()['application/hal+json'];
        $schema = $content->getSchema();

        $this->assertEquals(
            HalJson::HAL_ITEM,
            $schema->getAllOf()[0]['$ref']
        );
        $this->assertEquals(
            Schema::SCHEMA . 'Employee-Read',
            $schema->getAllOf()[1]['$ref']
        );
    }
}