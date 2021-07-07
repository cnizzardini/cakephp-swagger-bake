<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\Xml;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;

class XmlTest extends TestCase
{
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
        $this->router = new Router();

        $this->config = new Configuration([
            'prefix' => '/',
            'yml' => '/config/swagger-with-generic-collection.yml',
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
        $schema = (new Xml('#/components/schemas/thing', $swagger))->buildSchema('index');
        $this->assertEquals(
            '#/x-swagger-bake/components/schemas/Generic-Collection',
            $schema->getAllOf()[0]['$ref']
        );
        $this->assertArrayHasKey('data', $schema->getProperties());
    }
}