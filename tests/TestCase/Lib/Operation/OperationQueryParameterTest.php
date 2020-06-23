<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagDto;
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
                (new Operation())->setHttpMethod('GET'),
                [
                    new SwagQuery([
                        'name' => 'testName',
                        'type' => 'string',
                        'description' => 'test desc',
                        'required' => true,
                        'enum' => ['one','two'],
                        'allowReserved' => true,
                        'allowEmptyValue' => true,
                        'deprecated' => true,
                        'format' => 'date-time',
                        'example' => 'test example'
                    ]),
                ]
            );

        $param = $operation->getParameters()[0];
        $this->assertEquals('testName', $param->getName());
        $this->assertEquals('test desc', $param->getDescription());
        $this->assertTrue($param->isAllowReserved());
        $this->assertTrue($param->isAllowEmptyValue());
        $this->assertTrue($param->isRequired());
        $this->assertTrue($param->isDeprecated());
        $this->assertEquals('test example', $param->getExample());
        $schema = $param->getSchema();
        $this->assertCount(2, $schema->getEnum());
        $this->assertEquals('string', $schema->getType());
        $this->assertEquals('date-time', $schema->getFormat());
    }

    public function testGetOperationWithAllAnnotations()
    {
        $operation = (new OperationQueryParameter())
            ->getOperationWithQueryParameters(
                (new Operation())->setHttpMethod('GET'),
                [
                    new SwagPaginator(),
                    new SwagQuery([
                        'name' => 'test',
                        'type' => 'string',
                        'description' => '',
                        'required' => false,
                    ]),
                    new SwagDto(['class' => '\SwaggerBakeTest\App\Dto\EmployeeData'])
                ]
            );

        $parameters = $operation->getParameters();
        $this->assertCount(7, $parameters);

        $param = reset($parameters);
        $this->assertEquals('page', $param->getName());
        $this->assertEquals('query', $param->getIn());

        $param  = $parameters[4];
        $this->assertEquals('test', $param->getName());
        $this->assertEquals('query', $param->getIn());

        $param = end($parameters);
        $this->assertEquals('firstName', $param->getName());
        $this->assertEquals('query', $param->getIn());
    }
}