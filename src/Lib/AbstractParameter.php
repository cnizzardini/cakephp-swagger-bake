<?php

namespace SwaggerBake\Lib;

use Cake\Routing\Route\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

class AbstractParameter
{
    protected $route;
    protected $reader;
    protected $className;
    protected $actionName;
    protected $controller;
    protected $reflectionClass;
    protected $reflectionMethods;

    public function __construct(Route $route, Configuration $config)
    {
        $this->namespace = $config->getNamespace();
        $this->route = $route;

        $defaults = (array) $this->route->defaults;
        $this->actionName = $defaults['action'];
        $this->className = $defaults['controller'] . 'Controller';

        $this->controller = $this->namespace . 'Controller\\' . $this->className;
        $instance = new $this->controller;

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