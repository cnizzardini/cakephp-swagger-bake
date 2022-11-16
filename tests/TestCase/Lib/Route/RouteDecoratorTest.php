<?php

namespace SwaggerBake\Test\TestCase\Lib\Route;

use Cake\Routing\Route\Route;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Route\RouteDecorator;

class RouteDecoratorTest extends TestCase
{
    /**
     * When `__construct` is called, the route is properly decorated.
     */
    public function test(): void
    {
        $defaults = [
            'plugin' => null,
            'prefix' => null,
            'controller' => 'Departments',
            'action' => 'index',
            '_method' => 'GET'
        ];

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
    }

    /**
     * When `__construct` is called and the route is for a plugin, the controller FQN is found.
     */
    public function test_with_plugin(): void
    {
        $defaults = [
            'plugin' => 'Demo',
            'prefix' => null,
            'controller' => 'Test',
            'action' => 'index',
            '_method' => ['GET', 'POST']
        ];

        $routeDecorator = (new RouteDecorator(new Route('/test/template', $defaults)));
        $this->assertEquals($defaults['plugin'], $routeDecorator->getPlugin());
        $this->assertEquals($defaults['_method'], $routeDecorator->getMethods());
        $this->assertEquals(
            'Demo\Controller\TestController',
            $routeDecorator->getControllerFqn()
        );
    }

    /**
     * When `__construct` is called and the route template uses snake_case, the Controller FQN is found.
     */
    public function test_snake_case_controller_names(): void
    {
        $routeDecorator = (new RouteDecorator(new Route('/department_employees', [
            '_method' => ['GET', 'POST',],
            'controller' => 'department_employees',
        ])));
        $this->assertIsString($routeDecorator->getControllerFqn());
        $this->assertEquals(
            'SwaggerBakeTest\\App\\Controller\\DepartmentEmployeesController',
            $routeDecorator->getControllerFqn()
        );
    }

    /**
     * When `__construct` is called with a route prefix containing a forward `/` slash, the slash is flipped `\` and
     * the controller FQN is found.
     */
    public function test_prefixes_with_forward_slash(): void
    {
        $defaults = [
            'plugin' => null,
            'prefix' => 'Api/V1',
            'controller' => 'Departments',
            'action' => 'index',
            '_method' => 'GET',
        ];
        $routeDecorator = (new RouteDecorator(new Route('/departments', $defaults)));
        $this->assertIsString($routeDecorator->getControllerFqn());
        $this->assertEquals(
            'SwaggerBakeTest\\App\\Controller\\Api\V1\DepartmentsController',
            $routeDecorator->getControllerFqn()
        );
    }
}