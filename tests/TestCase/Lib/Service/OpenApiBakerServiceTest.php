<?php

namespace SwaggerBake\Test\TestCase\Lib\Service;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Service\OpenApiBakerService;
use SwaggerBake\Lib\Swagger;

class OpenApiBakerServiceTest extends TestCase
{
    public function test_warnings(): void
    {
        $mock = $this->createPartialMock(Swagger::class, [
            'build',
            'writeFile',
            'getOperationsWithNoHttp20x'
        ]);


        $mock->expects($this->once())->method('build')->willReturn($mock);
        $mock->expects($this->once())->method('writeFile');

        $mock
            ->expects($this->once())
            ->method('getOperationsWithNoHttp20x')
            ->willReturn([
                (new Operation('some-operation-id', 'GET')),
                (new Operation('other-operation-id', 'GET')),
            ]);

        $service = new OpenApiBakerService();
        $service->bake($mock, '/test');
        $this->assertCount(2, $service->getWarnings());
    }
}