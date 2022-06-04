<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use PHPStan\BetterReflection\Reflection\ReflectionAttribute;
use SwaggerBake\Lib\Annotation\SwagSecurity;
use SwaggerBake\Lib\Attribute\OpenApiSecurity;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationSecurity;

class OperationSecurityTest extends TestCase
{
    public function test_from_security_attribute(): void
    {
        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->once())
            ->method(
                'getAttributes'
            )
            ->with(OpenApiSecurity::class)
            ->will(
                $this->returnValue([
                    new ReflectionAttribute(OpenApiSecurity::class, [
                        'name' => 'BearerAuth',
                        'scopes' => ['A', 'B'],
                    ]),
                ])
            );

        $operationSecurity = new OperationSecurity(
            new Operation('hello', 'get'),
            $mockReflectionMethod,
        );

        $operation = $operationSecurity->getOperationWithSecurity();
        $securities = $operation->getSecurity();
        $security = reset($securities);
        $this->assertEquals('BearerAuth', $security->getName());
        $this->assertCount(2, $security->getScopes());
    }
}