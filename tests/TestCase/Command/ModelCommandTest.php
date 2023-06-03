<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class ModelCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public array $fixtures = [
        'plugin.SwaggerBake.Departments',
        'plugin.SwaggerBake.Employees',
    ];

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('SwaggerBakeTest\App');
        $this->useCommandRunner();
    }

    public function test_execute(): void
    {
        $this->exec('swagger models');
        $this->assertOutputContains('SwaggerBake is checking your models...');
        $this->assertOutputContains('- Department');
        $this->assertOutputContains('id');
        $this->assertOutputContains('name');
    }
}