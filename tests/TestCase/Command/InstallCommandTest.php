<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

class InstallCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var string
     */
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

    public function test(): void
    {
        $this->exec('swagger install --config_test ' . $this->configDir, ['Y','/']);
        $this->assertOutputContains('Installation Complete!');
        $this->assertFileExists($this->configDir . 'swagger.yml');
        $this->assertFileExists($this->configDir . 'swagger_bake.php');
    }

    public function test_do_not_continue(): void
    {
        $this->exec('swagger install', ['N']);
        $this->assertOutputContains('Exiting install');
    }

    public function test_do_not_overwrite_existing(): void
    {
        $this->exec('swagger install', ['Y','N']);
        $this->assertOutputContains('SwaggerBake Install');
        $this->assertExitError();
    }

    public function test_assets_directory_not_found(): void
    {
        $this->exec('swagger install --config_test /config/no-exists --assets_test /nope');
        $this->assertErrorContains('Unable to locate assets directory, please install manually');
        $this->assertExitError();
    }

    public function test_file_copy_fails(): void
    {
        $this->exec('swagger install --config_test /config/no-exists', ['Y','/']);
        $this->assertExitError();
    }

    public function test_invalid_api_path_prefix(): void
    {
        $this->exec('swagger install --config_test ' . $this->configDir, ['Y','\ sdf sdf ']);
        $this->assertExitError();
    }
}