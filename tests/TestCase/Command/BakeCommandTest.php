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
        unlink(WWW_ROOT . DS . 'swagger.json');
        $this->exec('swagger bake');
        $this->assertOutputContains('Running...');
        $this->assertOutputContains('Swagger File Created:');
    }
}