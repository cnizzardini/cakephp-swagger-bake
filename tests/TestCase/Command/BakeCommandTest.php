<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

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

    public function testExecute()
    {
        $path = WWW_ROOT . DS . 'swagger.json';
        unlink($path);
        $this->exec('swagger bake');
        $this->assertOutputContains('Running...');
        $this->assertOutputContains("Swagger File Created: $path");
    }
}