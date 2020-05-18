<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagHeader;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationHeader;

class OperationHeaderTest extends TestCase
{
    public function testGetOperationWithHeaders()
    {
        $operation = (new OperationHeader())
            ->getOperationWithHeaders(
                new Operation(),
                [new SwagHeader(['name' => 'X-HEADER','type' => 'string', 'description' => '', 'required' => false])]
            );

        $parameters = $operation->getParameters();
        $param = reset($parameters);
        $this->assertEquals('X-HEADER', $param->getName());
        $this->assertEquals('header', $param->getIn());
    }
}