<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationPath;

class OperationPathTest extends TestCase
{
    public function testGetOperationWithHeaders()
    {
        $route = (new ExpressiveRoute())
            ->setName('employees:view')
            ->setController('Employees')
            ->setAction('view')
            ->setMethods(['GET'])
            ->setTemplate('/api/employees/:id')
        ;

        $operation = (new OperationPath())
            ->getOperationWithPathParameters(
                new Operation(),
                $route
            );

        $parameters = $operation->getParameters();
        $param = reset($parameters);
        $this->assertEquals('id', $param->getName());
        $this->assertEquals('path', $param->getIn());
    }
}