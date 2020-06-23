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
                [
                    new SwagHeader([
                        'name' => 'X-HEADER',
                        'type' => 'string',
                        'description' => 'test desc',
                        'required' => true,
                        'explode' => true,
                        'allowEmptyValue' => true,
                        'deprecated' => true,
                        'format' => 'date',
                        'example' => 'test example'
                    ])
                ]
            );

        $param = $operation->getParameters()[0];
        $this->assertEquals('X-HEADER', $param->getName());
        $this->assertEquals('header', $param->getIn());
        $this->assertEquals('test desc', $param->getDescription());
        $this->assertTrue($param->isRequired());
        $this->assertTrue($param->isExplode());
        $this->assertTrue($param->isAllowEmptyValue());
        $this->assertTrue($param->isDeprecated());
        $this->assertEquals('test example', $param->getExample());
        $schema = $param->getSchema();
        $this->assertEquals('date', $schema->getFormat());
    }
}