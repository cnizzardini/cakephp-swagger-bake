<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;
use PHPStan\BetterReflection\Reflection\ReflectionAttribute;
use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagPaginator;
use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\Attribute\OpenApiDto;
use SwaggerBake\Lib\Attribute\OpenApiPaginator;
use SwaggerBake\Lib\Attribute\OpenApiQueryParam;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\Operation\OperationQueryParameter;

class OperationQueryParameterTest extends TestCase
{
    public function test_open_api_query_param(): void
    {
        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([

                    ]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiQueryParam::class, [
                            'name' => 'testName',
                            'type' => 'string',
                            'description' => 'test desc',
                            'required' => true,
                            'enum' => ['one','two'],
                            'allowEmptyValue' => true,
                            'deprecated' => true,
                            'format' => 'date-time',
                            'example' => 'test example'
                        ])
                    ]),
                    $this->returnValue([

                    ]),
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            operation: (new Operation())->setHttpMethod('GET'),
            controller: new Controller(),
            refMethod: $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();

        $param = $operation->getParameterByTypeAndName('query', 'testName');
        $this->assertEquals('testName', $param->getName());
        $this->assertEquals('test desc', $param->getDescription());
        $this->assertTrue($param->isAllowEmptyValue());
        $this->assertTrue($param->isRequired());
        $this->assertTrue($param->isDeprecated());
        $this->assertEquals('test example', $param->getExample());
        $schema = $param->getSchema();
        $this->assertCount(2, $schema->getEnum());
        $this->assertEquals('string', $schema->getType());
        $this->assertEquals('date-time', $schema->getFormat());
    }

    public function test_all_attributes_in_one(): void
    {
        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiPaginator::class, [])
                    ]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiQueryParam::class, [
                            'name' => 'testName',
                            'type' => 'string',
                            'description' => 'test desc',
                            'required' => true,
                            'enum' => ['one','two'],
                            'allowEmptyValue' => true,
                            'deprecated' => true,
                            'format' => 'date-time',
                            'example' => 'test example'
                        ])
                    ]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiDto::class, [
                            'class' => '\SwaggerBakeTest\App\Dto\EmployeeData',
                        ])
                    ]),
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            operation: (new Operation())->setHttpMethod('GET'),
            controller: new Controller(),
            refMethod: $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();

        $parameters = $operation->getParameters();

        $this->assertCount(10, $parameters);
    }

    public function test_open_api_paginator(): void
    {
        $enums = ['A','B'];
        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiPaginator::class, [
                            'sortEnum' => $enums,
                        ]),
                    ]),
                    $this->returnValue([

                    ]),
                    $this->returnValue([

                    ])
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            operation: (new Operation())->setHttpMethod('GET'),
            controller: new Controller(),
            refMethod: $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();
        $parameter = $operation->getParameterByTypeAndName('query', 'sort');
        $this->assertEquals($enums, $parameter->getSchema()->getEnum());
    }

    public function test_open_api_parameter_using_ref(): void
    {
        $ref = '#/x-swagger-bake/components/parameters/paginatorPage';

        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([

                    ]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiQueryParam::class, [
                            'ref' => $ref,
                        ])
                    ]),
                    $this->returnValue([

                    ])
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            (new Operation())->setHttpMethod('GET'),
            new Controller(),
            null,
            $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();
        $key = 'x-swagger-bake-components-parameters-paginatorPage';
        $parameter = $operation->getParameterByTypeAndName('query', $key);

        $this->assertInstanceOf(Parameter::class, $parameter);
        $this->assertEquals($ref, $parameter->getRef());
    }
}