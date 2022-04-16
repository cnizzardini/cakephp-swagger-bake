<?php

namespace SwaggerBake\Test\TestCase\Lib\Service;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Exception\InstallException;
use SwaggerBake\Lib\Service\InstallerService;
use SwaggerBake\Lib\Utility\FileUtility;

class InstallerServiceTest extends TestCase
{
    public function test_invalid_prefix_should_throw_exception(): void
    {
        $this->expectException(InstallException::class);
        (new InstallerService())->install('nope');
        $this->assertStringContainsString('Invalid prefix', $this->getExpectedExceptionMessage());

        $this->expectException(InstallException::class);
        (new InstallerService())->install('/almost-valid!@#$%^&*()+{};:"');
        $this->assertStringContainsString('Invalid prefix', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exception_when_assets_not_found(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(false);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('Assets directory', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exceptions_on_existing_config_files(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(true);
        $mock->expects($this->once())->method('fileExists')->willReturn(true);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('The installer found an existing', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exceptions_on_copy_yaml(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(true);
        $mock->expects($this->exactly(2))->method('fileExists')->willReturn(false);
        $mock->expects($this->once())->method('copy')->willReturn(false);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('Error copying base OpenAPI YAML', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exceptions_on_copy_config(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(true);
        $mock->expects($this->exactly(2))->method('fileExists')->willReturn(false);
        $mock->expects($this->exactly(2))->method('copy')->willReturnOnConsecutiveCalls(true, false);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('Error copying swagger_bake config', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exceptions_on_getting_yaml_content(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(true);
        $mock->expects($this->exactly(2))->method('fileExists')->willReturn(false);
        $mock->expects($this->exactly(2))->method('copy')->willReturnOnConsecutiveCalls(true, true);
        $mock->expects($this->once())->method('getContents')->willReturn(false);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('Error reading YAML', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exceptions_on_putting_yaml_content(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(true);
        $mock->expects($this->exactly(2))->method('fileExists')->willReturn(false);
        $mock->expects($this->exactly(2))->method('copy')->willReturnOnConsecutiveCalls(true, true);
        $mock->expects($this->once())->method('getContents')->willReturn('string');
        $mock->expects($this->once())->method('putContents')->willReturn(false);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('Error writing OpenAPI YAML', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exceptions_on_getting_config_content(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(true);
        $mock->expects($this->exactly(2))->method('fileExists')->willReturn(false);
        $mock->expects($this->exactly(2))->method('copy')
            ->willReturnOnConsecutiveCalls(true, true);
        $mock->expects($this->exactly(2))->method('getContents')
            ->willReturnOnConsecutiveCalls('string', false);
        $mock->expects($this->once())->method('putContents')->willReturn(1);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('Error reading config', $this->getExpectedExceptionMessage());
    }

    public function test_install_should_throw_exceptions_on_putting_config_content(): void
    {
        $mock = $this->createMock(FileUtility::class);
        $mock->expects($this->once())->method('isDir')->willReturn(true);
        $mock->expects($this->exactly(2))->method('fileExists')->willReturn(false);
        $mock->expects($this->exactly(2))->method('copy')
            ->willReturnOnConsecutiveCalls(true, true);
        $mock->expects($this->exactly(2))->method('getContents')
            ->willReturnOnConsecutiveCalls('string', 'string');
        $mock->expects($this->exactly(2))->method('putContents')
            ->willReturnOnConsecutiveCalls(1, false);

        $this->expectException(InstallException::class);
        (new InstallerService(CONFIG, $mock))->install('/');
        $this->assertStringContainsString('Error writing config', $this->getExpectedExceptionMessage());
    }
}