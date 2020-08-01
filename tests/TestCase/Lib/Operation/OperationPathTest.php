<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use Cake\Routing\Route\Route;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationPath;

class OperationPathTest extends TestCase
{
    public function testGetOperationWithHeaders()
    {
        $routeDecorator = new RouteDecorator(
            new Route('/api/employees/:id', [
                '_method' => ['GET'],
                'plugin' => '',
                'controller' => 'Employees',
                'action' => 'view'
            ])
        );

        $operation = (new OperationPath(new Operation(), $routeDecorator))->getOperationWithPathParameters();

        $parameters = $operation->getParameters();
        $param = reset($parameters);
        $this->assertEquals('id', $param->getName());
        $this->assertEquals('path', $param->getIn());
    }
}