<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class InstallCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    private $configDir;

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('SwaggerBakeTest\App');
        $this->useCommandRunner();

        $this->configDir = CONFIG . 'testing' . DS;
        foreach (scandir($this->configDir) as $file) {
            if (is_file($this->configDir . $file)) {
                unlink($this->configDir . $file);
            }
        }
    }

    public function testExecute()
    {
        $this->exec('swagger install --config_test ' . $this->configDir, ['Y','/api']);
        $this->assertOutputContains('Installation Complete!');
        $this->assertFileExists($this->configDir . 'swagger.yml');
        $this->assertFileExists($this->configDir . 'swagger_bake.php');
    }

    public function testExecuteWithExitDoNotOverwriteExisting()
    {
        $this->exec('swagger install', ['Y','N']);
        $this->assertOutputContains('SwaggerBake Install');
        $this->assertExitError();
    }

    public function testExecuteFileCopyFails()
    {
        $this->exec('swagger install --config_test /config/no-exists', ['Y','/api']);
        $this->assertExitError();
    }

    public function testExecuteInvalidApiPathPrefix()
    {
        $this->exec('swagger install --config_test ' . $this->configDir, ['Y','\ sdf sdf ']);
        $this->assertExitError();
    }
}