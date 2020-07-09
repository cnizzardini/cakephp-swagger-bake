<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ModelCommandTest extends TestCase
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
        $this->exec('swagger models');
        $this->assertOutputContains('SwaggerBake is checking your models...');
        $this->assertOutputContains('- Department');
        $this->assertOutputContains('id');
        $this->assertOutputContains('name');
    }

    public function testExecuteNoModelsFoundErrorMessage()
    {
        $this->exec('swagger models --prefix /nope');
        $this->assertExitError();
    }
}