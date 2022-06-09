<?php

namespace SwaggerBake\Test\TestCase\Lib\Factory;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\SwaggerFactory;

class SwaggerFactoryTest extends TestCase
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

    public function test_create_method_throws_swagger_runtime_exception(): void
    {
        $this->expectException(SwaggerBakeRunTimeException::class);
        (new SwaggerFactory($this->config))->create();
    }
}