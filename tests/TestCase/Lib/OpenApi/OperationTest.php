<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\PathSecurity;
use SwaggerBake\Lib\OpenApi\Response;

class OperationTest extends TestCase
{
    public function test_get_set(): void
    {
        $path = (new Operation())
            ->setDescription($desc = '1')
            ->setSummary($s = '2')
            ->setResponses([new Response()])
            ->setSecurity([(new PathSecurity())->setName('test')->setScopes(['test'])])
        ;

        $this->assertEquals($desc, $path->getDescription());
        $this->assertEquals($s, $path->getSummary());
        $this->assertInstanceOf(Response::class, $path->getResponses()[0]);
        $this->assertInstanceOf(PathSecurity::class, $path->getSecurity()['test']);
    }

    public function test_hasSuccessResponseCode(): void
    {
        $this->assertTrue(
            (new Operation())->setResponses([(new Response())->setCode('2XX')])->hasSuccessResponseCode()
        );
    }

    public function test_setHttpMethod_throws_invalid_arg_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Operation())->setHttpMethod('nope');
    }

    public function test_getParameterByTypeAndName_throws_invalid_arg_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Operation())->getParameterByTypeAndName('nope', 'nope');
    }

    public function test_getParameterByTypeAndName_throws_invalid_arg_exception_next(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Operation())->getParameterByTypeAndName('query', 'nope');
    }
}