<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class InstallCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('SwaggerBakeTest\App');
        $this->useCommandRunner();
    }

    public function testExecute()
    {
        $this->exec('swagger install', ['Y','N']);
        $this->assertOutputContains('SwaggerBake Install');
    }
}