<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Attribute\OpenApiHeader;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationHeader;
use SwaggerBake\Test\TestCase\Helper\ReflectionAttributeTrait;

class OperationHeaderTest extends TestCase
{
    use ReflectionAttributeTrait;

    public function test_get_operation_with_headers(): void
    {
        $mockReflectionMethod = $this->mockReflectionMethod(OpenApiHeader::class, [
            'name' => 'X-HEADER',
            'type' => 'string',
            'description' => 'test desc',
            'isRequired' => true,
            'explode' => true,
            'allowEmptyValue' => true,
            'isDeprecated' => true,
            'format' => 'date',
            'example' => 'test example',
        ]);

        $operation = (new OperationHeader())
            ->getOperationWithHeaders(
                new Operation('hello', 'get'),
                $mockReflectionMethod
            );

        $param = $operation->getParameterByTypeAndName('header', 'X-HEADER');

        $this->assertEquals('X-HEADER', $param->getName());
        $this->assertEquals('header', $param->getIn());
        $this->assertEquals('test desc', $param->getDescription());
        $this->assertTrue($param->isRequired());
        $this->assertTrue($param->isExplode());
        $this->assertTrue($param->isDeprecated());
        $this->assertEquals('test example', $param->getExample());
        $schema = $param->getSchema();
        $this->assertEquals('date', $schema->getFormat());
    }
}