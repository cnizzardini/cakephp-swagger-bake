<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagForm;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationForm;

class OperationFormTest extends TestCase
{
    public function testSwagFormGetOperationWithFormProperties()
    {
        $operation = (new OperationForm())
            ->getOperationWithFormProperties(
                (new Operation())->setHttpMethod('POST'),
                [
                    new SwagForm(['name' => 'test', 'type' => 'string', 'description' => '', 'required' => false])
                ]
            );

        $requestBody = $operation->getRequestBody();
        $form = $requestBody->getContentByType('application/x-www-form-urlencoded');
        $properties = $form->getSchema()->getProperties();

        $this->assertArrayHasKey('test', $properties);;
    }

    public function testSwagDtoGetOperationWithFormProperties()
    {
        $operation = (new OperationForm())
            ->getOperationWithFormProperties(
                (new Operation())->setHttpMethod('POST'),
                [
                    new SwagDto(['class' => '\SwaggerBakeTest\App\Dto\EmployeeData'])
                ]
            );

        $requestBody = $operation->getRequestBody();
        $form = $requestBody->getContentByType('application/x-www-form-urlencoded');
        $properties = $form->getSchema()->getProperties();

        $this->assertArrayHasKey('lastName', $properties);
        $this->assertArrayHasKey('firstName', $properties);
    }
}