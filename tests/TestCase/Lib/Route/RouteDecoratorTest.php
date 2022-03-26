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
            'controller' => 'Test',
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
        $this->assertEquals([$defaults['_method']], $routeDecorator->getMethods());
        $this->assertEquals('SwaggerBakeTest\App\Controller\TestController', $routeDecorator->getControllerFqn());

        // Plugin controller + multiple methods
        $defaults['plugin'] = 'TestPlugin';
        $defaults['_method'] = ['GET', 'POST'];
        $routeDecorator = (new RouteDecorator(new Route('/test/template', $defaults)));
        $this->assertEquals($defaults['plugin'], $routeDecorator->getPlugin());
        $this->assertEquals($defaults['_method'], $routeDecorator->getMethods());
        $this->assertEquals('TestPlugin\Controller\TestController', $routeDecorator->getControllerFqn());
    }
}