<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

class BakeCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public $fixtures = [
        'plugin.SwaggerBake.Departments'
    ];

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('SwaggerBakeTest\App');
        $this->useCommandRunner();
    }

    public function test_execute(): void
    {
        $path = WWW_ROOT . DS . 'swagger.json';
        unlink($path);
        $this->exec('swagger bake');
        $this->assertOutputContains('Running...');
        $this->assertOutputContains("Swagger File Created: $path");
    }

    public function test_write_failure(): void
    {
        $this->expectException(SwaggerBakeRunTimeException::class);
        $this->exec('swagger bake --output /etc/no-no/swagger.json');
    }

    public function test_config_not_found_exception(): void
    {
        $this->expectException(SwaggerBakeRunTimeException::class);
        $this->exec('swagger bake --config nope');
    }
}