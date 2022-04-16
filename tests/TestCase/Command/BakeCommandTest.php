<?php

namespace SwaggerBake\Test\TestCase\Command;

use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Service\OpenApiBakerService;

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

    public function test_execute(): void
    {
        $path = WWW_ROOT . DS . 'swagger.json';
        unlink($path);
        $this->exec('swagger bake');
        $this->assertOutputContains('Running...');
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
}