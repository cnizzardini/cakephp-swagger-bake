<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Exception\InstallException;
use SwaggerBake\Lib\Service\InstallerService;

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
        $files = scandir($this->configDir);
        if (!is_array($files)) {
            throw new \RuntimeException("Tests cannot be run because no files were found.");
        }
        foreach ($files as $file) {
            if (is_file($this->configDir . $file)) {
                unlink($this->configDir . $file);
            }
        }
    }

    public function test_install_should_work(): void
    {
        $this->mockService(InstallerService::class, function () {
            return new InstallerService($this->configDir);
        });

        $this->exec('swagger install', ['Y', '/']);
        $this->assertOutputContains('Installation Complete!');
        $this->assertFileExists($this->configDir . 'swagger.yml');
        $this->assertFileExists($this->configDir . 'swagger_bake.php');
    }

    public function test_do_not_continue(): void
    {
        $this->exec('swagger install', ['N']);
        $this->assertOutputContains('Exiting install');
    }

    public function test_user_can_skip_error(): void
    {
        $this->mockService(InstallerService::class, function () {
            $mock = $this->createMock(InstallerService::class);
            $matcher = $this->exactly(2);
            $mock
                ->expects($matcher)
                ->method('install')
                ->withAnyParameters()
                ->willReturnCallback(function () use ($matcher) {
                    if ($matcher->getInvocationCount() === 1) {
                        throw (new InstallException())->setQuestion("skip me");
                    }

                    return true;
                });
            return $mock;
        });

        $this->exec('swagger install', ['Y', '/', 'Y']);
        $this->assertOutputContains('Installation Complete!');
    }

    public function test_exception_is_printed_as_console_error(): void
    {
        $this->mockService(InstallerService::class, function () {
            $mock = $this->createMock(InstallerService::class);
            $mock
                ->expects($this->once())
                ->method('install')
                ->withAnyParameters()
                ->willThrowException(new InstallException("error message"));
            return $mock;
        });

        $this->exec('swagger install', ['Y', '/']);
        $this->assertOutputContains('error message');
    }
}