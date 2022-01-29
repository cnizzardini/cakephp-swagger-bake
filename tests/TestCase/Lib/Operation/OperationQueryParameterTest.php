<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Controller\Controller;
use Cake\TestSuite\TestCase;
use PHPStan\BetterReflection\Reflection\ReflectionAttribute;
use SwaggerBake\Lib\Attribute\OpenApiDto;
use SwaggerBake\Lib\Attribute\OpenApiPaginator;
use SwaggerBake\Lib\Attribute\OpenApiQueryParam;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\Operation\OperationQueryParameter;
use SwaggerBakeTest\App\Dto\EmployeeDataRequest;
use SwaggerBakeTest\App\Dto\EmployeeDataRequestConstructorPromotion;

class OperationQueryParameterTest extends TestCase
{
    public function test_openapi_query_param(): void
    {
        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiQueryParam::class, [
                            'name' => 'testName',
                            'type' => 'string',
                            'description' => 'test desc',
                            'isRequired' => true,
                            'enum' => ['one','two'],
                            'allowEmptyValue' => true,
                            'isDeprecated' => true,
                            'format' => 'date-time',
                            'example' => 'test example'
                        ])
                    ]),
                    $this->returnValue([]),
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            operation: (new Operation('hello', 'get'))->setHttpMethod('GET'),
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
                            'isRequired' => true,
                            'enum' => ['one','two'],
                            'allowEmptyValue' => true,
                            'isDeprecated' => true,
                            'format' => 'date-time',
                            'example' => 'test example'
                        ])
                    ]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiDto::class, [
                            'class' => '\SwaggerBakeTest\App\Dto\EmployeeDataRequest',
                        ])
                    ]),
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            operation: (new Operation('hello', 'get'))->setHttpMethod('GET'),
            controller: new Controller(),
            refMethod: $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();

        $parameters = $operation->getParameters();

        $this->assertCount(10, $parameters);
    }

    public function test_openapi_paginator(): void
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
                    $this->returnValue([]),
                    $this->returnValue([])
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            operation: new Operation('hello', 'get'),
            controller: new Controller(),
            refMethod: $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();
        $parameter = $operation->getParameterByTypeAndName('query', 'sort');
        $this->assertEquals($enums, $parameter->getSchema()->getEnum());
    }

    public function test_openapi_paginator_use_sort_text_input(): void
    {
        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiPaginator::class, [
                            'useSortTextInput' => true,
                        ]),
                    ]),
                    $this->returnValue([]),
                    $this->returnValue([])
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            operation: new Operation('hello', 'get'),
            controller: new Controller(),
            refMethod: $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();
        $this->assertArrayHasKey('#/x-swagger-bake/components/parameters/paginatorSort', $operation->getParameters());
    }

    public function test_openapi_paginator_with_component_sortable_fields(): void
    {
        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiPaginator::class, []),
                    ]),
                    $this->returnValue([]),
                    $this->returnValue([])
                )
            );

        $controller = new Controller();
        $controller->paginate['sortableFields'] = ['test'];

        $operationQueryParam = new OperationQueryParameter(
            operation: new Operation('hello', 'get'),
            controller: $controller,
            refMethod: $mockReflectionMethod
        );

        $operation = $operationQueryParam->getOperationWithQueryParameters();
        $parameter = $operation->getParameterByTypeAndName('query', 'sort');

        $this->assertEquals(['test'], $parameter->getSchema()->getEnum());
    }

    public function test_openapi_parameter_using_ref(): void
    {
        $ref = '#/x-swagger-bake/components/parameters/paginatorPage';

        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiQueryParam::class, [
                            'ref' => $ref,
                        ])
                    ]),
                    $this->returnValue([])
                )
            );

        $operationQueryParam = new OperationQueryParameter(
            new Operation('hello', 'get'),
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

    public function test_openapi_dto_query(): void
    {
        foreach ([EmployeeDataRequest::class, EmployeeDataRequestConstructorPromotion::class] as $class) {
            $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
            $mockReflectionMethod->expects($this->any())
                ->method(
                    'getAttributes'
                )
                ->will(
                    $this->onConsecutiveCalls(
                        $this->returnValue([]),
                        $this->returnValue([]),
                        $this->returnValue([
                            new ReflectionAttribute(OpenApiDto::class, [
                                'class' => $class,
                            ])
                        ]),
                    )
                );

            $operationQueryParam = new OperationQueryParameter(
                operation: (new Operation('hello', 'get'))->setHttpMethod('GET'),
                controller: new Controller(),
                refMethod: $mockReflectionMethod
            );

            $operation = $operationQueryParam->getOperationWithQueryParameters();

            $parameters = $operation->getParameters();
            $this->assertArrayHasKey('query:last_name', $parameters, "failed for $class");
            $this->assertArrayHasKey('query:first_name', $parameters, "failed for $class");
        }
    }

    public function test_dto_class_not_found_exception(): void
    {
        $this->expectException(SwaggerBakeRunTimeException::class);

        $mockReflectionMethod = $this->createPartialMock(\ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod->expects($this->any())
            ->method(
                'getAttributes'
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue([]),
                    $this->returnValue([]),
                    $this->returnValue([
                        new ReflectionAttribute(OpenApiDto::class, [
                            'class' => '\SwaggerBakeTest\App\Dto\Nope',
                        ])
                    ]),
                )
            );

        (new OperationQueryParameter(
            operation: (new Operation('hello', 'get'))->setHttpMethod('GET'),
            controller: new Controller(),
            refMethod: $mockReflectionMethod
        ))->getOperationWithQueryParameters();
    }
}