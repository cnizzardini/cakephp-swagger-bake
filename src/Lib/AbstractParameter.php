<?php

namespace SwaggerBake\Lib;

use Cake\Routing\Route\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

class AbstractParameter
{
    protected $route;
    protected $reader;
    protected $actionName;
    protected $reflectionClass;
    protected $reflectionMethods;

    public function __construct(Route $route, Configuration $config)
    {
        $this->namespace = $config->getNamespace();
        $this->route = $route;

        $defaults = (array) $this->route->defaults;
        $this->actionName = $defaults['action'];
        $className = $defaults['controller'] . 'Controller';

        $controller = $this->namespace . 'Controller\\' . $className;
        $instance = new $controller;

        $this->reflectionClass = new ReflectionClass($instance);
        $this->reflectionMethods = $this->reflectionClass->getMethods();

        $this->reader = new AnnotationReader();
    }

    protected function getMethods() : array
    {
        return array_filter($this->reflectionMethods, function ($method) {
            return $method->name == $this->actionName;
        });
    }
}