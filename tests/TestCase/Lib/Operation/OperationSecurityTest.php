<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagHeader;
use SwaggerBake\Lib\Annotation\SwagSecurity;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationHeader;
use SwaggerBake\Lib\Operation\OperationSecurity;

class OperationSecurityTest extends TestCase
{
    public function testGetOperationSecurity()
    {
        $operation = (new OperationSecurity())
            ->getOperationWithSecurity(
                new Operation(),
                [new SwagSecurity(['name' => 'BearerAuth' , 'scopes' => ['read','write']])]
            );

        $securities = $operation->getSecurity();
        $security = reset($securities);
        $this->assertEquals('BearerAuth', $security->getName());
        $this->assertCount(2, $security->getScopes());
    }
}