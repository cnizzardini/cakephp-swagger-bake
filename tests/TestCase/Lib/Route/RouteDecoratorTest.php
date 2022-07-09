<?php

namespace SwaggerBake\Test\TestCase\Lib\Route;

use Cake\Routing\Route\Route;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Route\RouteDecorator;

class RouteDecoratorTest extends TestCase
{
    public function test_construct(): void
    {
        $defaults = [
            'plugin' => null,
            'prefix' => null,
            'controller' => 'Departments',
            'action' => 'index',
            '_method' => 'GET'
        ];

        // App controller
        $routeDecorator = (new RouteDecorator(new Route('/test/template', $defaults)));
        $this->assertEquals($defaults['plugin'], $routeDecorator->getPlugin());
        $this->assertEquals($defaults['prefix'], $routeDecorator->getPrefix());
        $this->assertEquals($defaults['controller'], $routeDecorator->getController());
        $this->assertEquals($defaults['action'], $routeDecorator->getAction());
        $this->assertEquals([$defaults['_method']], $routeDecorator->getMethods());
        $this->assertEquals(
            'SwaggerBakeTest\App\Controller\DepartmentsController',
            $routeDecorator->getControllerFqn()
        );

        // Plugin controller + multiple methods
        $defaults['plugin'] = 'Demo';
        $defaults['_method'] = ['GET', 'POST'];
        $defaults['controller'] = 'Test';
        $routeDecorator = (new RouteDecorator(new Route('/test/template', $defaults)));
        $this->assertEquals($defaults['plugin'], $routeDecorator->getPlugin());
        $this->assertEquals($defaults['_method'], $routeDecorator->getMethods());
        $this->assertEquals(
            'Demo\Controller\TestController',
            $routeDecorator->getControllerFqn()
        );
    }

    public function test_snake_case_controller_names(): void
    {
        $routeDecorator = (new RouteDecorator(new Route('/department_employees', [
            '_method' => ['GET', 'POST',],
            'controller' => 'department_employees',
        ])));
        $this->assertIsString($routeDecorator->getControllerFqn());
        $this->assertEquals(
            'App\\Controller\\DepartmentEmployeesController',
            $routeDecorator->getControllerFqn()
        );
    }
}