<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Service\OpenApiBakerService;

class BakeCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public array $fixtures = [
        'plugin.SwaggerBake.Departments'
    ];

    public function setUp() : void
    {
        parent::setUp();
        $this->setAppNamespace('SwaggerBakeTest\App');
    }

    public function test_execute(): void
    {
        $path = WWW_ROOT . '/' . 'swagger.json';
        $this->exec('swagger bake');
        $this->assertOutputContains('Running...');
        if ($this->isOnWindows()) {
            // WWW_ROOT and presented path will not be consistent as `/webroot/swagger.json` comes from config
            $basePath = str_replace('\webroot', '/webroot', WWW_ROOT);
            $this->assertOutputContains('Swagger File Created: ');
            $this->assertOutputContains($basePath);
            $this->assertOutputContains('swagger.json');
            return;
        }
        $this->assertOutputContains("Swagger File Created: $path");
    }

    public function test_exception_outputs_error(): void
    {
        $this->mockService(OpenApiBakerService::class, function () {
            $mock = $this->createMock(OpenApiBakerService::class);
            $mock
                ->expects($this->once())
                ->method('bake')
                ->willThrowException(new SwaggerBakeRunTimeException('test error'));
            return $mock;
        });

        $this->exec('swagger bake');
        $this->assertOutputContains('test error');
    }

    public function test_exception_outputs_warning(): void
    {
        $this->mockService(OpenApiBakerService::class, function () {
            $mock = $this->createMock(OpenApiBakerService::class);
            $mock
                ->expects($this->once())
                ->method('bake')
                ->willReturn('test');
            $mock
                ->expects($this->once())
                ->method('getWarnings')
                ->willReturn(['warning one', 'warning two']);
            return $mock;
        });

        $this->exec('swagger bake');
        $this->assertOutputContains('2 warning(s) were detected');
        $this->assertOutputContains('warning one');
        $this->assertOutputContains('warning two');
    }

    public function test_config_not_found_exception(): void
    {
        $this->expectException(SwaggerBakeRunTimeException::class);
        $this->exec('swagger bake --config nope');
    }

    private function isOnWindows(): bool
    {
        return str_starts_with(strtolower(PHP_OS), 'win');
    }
}