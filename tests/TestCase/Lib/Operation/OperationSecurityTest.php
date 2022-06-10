<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Attribute\OpenApiSecurity;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationSecurity;
use SwaggerBake\Test\TestCase\Helper\ReflectionAttributeTrait;

class OperationSecurityTest extends TestCase
{
    use ReflectionAttributeTrait;

    public function test_from_security_attribute(): void
    {
        $mockReflectionMethod = $this->mockReflectionMethod(OpenApiSecurity::class, [
            'name' => 'BearerAuth',
            'scopes' => ['A', 'B'],
        ]);

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