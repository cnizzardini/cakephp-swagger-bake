<?php

namespace SwaggerBake\Lib;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ExpressiveRoute;

class AbstractParameter
{
    protected $route;
    protected $reader;
    protected $className;
    protected $actionName;
    protected $controller;
    protected $reflectionClass;
    protected $reflectionMethods;
    protected $config;

    public function __construct(ExpressiveRoute $route, Configuration $config)
    {
        $this->config = $config;
        $this->route = $route;

        $this->actionName = $this->route->getAction();
        $this->className = $this->route->getController() . 'Controller';

        $this->controller = $this->getControllerFromNamespaces($this->className);
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

    private function getControllerFromNamespaces(string $className) : ?string
    {
        $namespaces = $this->config->getNamespaces();

        if (!isset($namespaces['controllers']) || !is_array($namespaces['controllers'])) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.controllers'
            );
        }

        foreach ($namespaces['controllers'] as $namespace) {
            $entity = $namespace . 'Controller\\' . $className;
            if (class_exists($entity, true)) {
                return $entity;
            }
        }

        return null;
    }
}