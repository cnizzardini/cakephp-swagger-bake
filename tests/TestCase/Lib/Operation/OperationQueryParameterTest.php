<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagPaginator;
use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationQueryParameter;

class OperationQueryParameterTest extends TestCase
{
    public function testGetOperationWithQueryParameters()
    {
        $operation = (new OperationQueryParameter())
            ->getOperationWithQueryParameters(
                new Operation(),
                [
                    new SwagPaginator(),
                    new SwagQuery(['name' => 'test', 'type' => 'string', 'description' => '', 'required' => false])
                ]
            );

        $parameters = $operation->getParameters();
        $this->assertCount(5, $parameters);
        $param = reset($parameters);
        $this->assertEquals('page', $param->getName());
        $this->assertEquals('query', $param->getIn());
        $param = end($parameters);
        $this->assertEquals('test', $param->getName());
        $this->assertEquals('query', $param->getIn());
    }
}