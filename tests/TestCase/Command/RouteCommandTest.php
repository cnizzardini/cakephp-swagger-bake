<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class RouteCommandTest extends TestCase
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
        $this->exec('swagger routes');
        $this->assertOutputContains('SwaggerBake is checking your routes...');
        $this->assertOutputContains('departments:index');
    }
}