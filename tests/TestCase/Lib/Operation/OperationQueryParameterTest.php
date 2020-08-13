<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagPaginator;
use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\Operation\OperationQueryParameter;

class OperationQueryParameterTest extends TestCase
{
    public function testSwagQueryParameters()
    {
        $operationQueryParam = new OperationQueryParameter(
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
            ],
            new Controller()
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();

        $param = $operation->getParameterByTypeAndName('query', 'testName');
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

    public function testSwagPaginatorAndSwagQueryAndSwagDto()
    {
        $operationQueryParam = new OperationQueryParameter(
            (new Operation())->setHttpMethod('GET'),
            [
                new SwagPaginator([]),
                new SwagQuery([
                    'name' => 'test',
                    'type' => 'string',
                    'description' => '',
                    'required' => false,
                ]),
                new SwagDto(['class' => '\SwaggerBakeTest\App\Dto\EmployeeData'])
            ],
            new Controller()
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();

        $parameters = $operation->getParameters();
        $this->assertCount(10, $parameters);
    }

    public function testSwagPaginatorSortEnum()
    {
        $enums = ['A','B'];
        $operationQueryParam = new OperationQueryParameter(
            (new Operation())->setHttpMethod('GET'),
            [
                new SwagPaginator(['sortEnum'=> $enums]),
            ],
            new Controller()
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();
        $parameter = $operation->getParameterByTypeAndName('query', 'sort');
        $this->assertEquals($enums, $parameter->getSchema()->getEnum());
    }
}