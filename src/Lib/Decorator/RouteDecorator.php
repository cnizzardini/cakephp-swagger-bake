<?php

namespace SwaggerBake\Lib\Decorator;

use Cake\Routing\Route\Route;

class RouteDecorator
{
    /** @var Route */
    private $route;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $plugin;

    /** @var string|null */
    private $controller;

    /** @var string|null */
    private $action;

    /** @var array */
    private $methods = [];

    /** @var string|null */
    private $template;

    public function __construct(Route $route)
    {
        $this->route;

        $defaults = (array) $route->defaults;

        $methods = $defaults['_method'];
        if (!is_array($defaults['_method'])) {
            $methods = explode(', ', $defaults['_method']);
        }

        $this
            ->setTemplate($route->template)
            ->setName($route->getName())
            ->setPlugin($defaults['plugin'])
            ->setController($defaults['controller'])
            ->setAction($defaults['action'])
            ->setMethods($methods)
        ;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @param Route $route
     * @return RouteDecorator
     */
    public function setRoute(Route $route): RouteDecorator
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName(string $name): RouteDecorator
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    /**
     * @param string|null $plugin
     * @return $this
     */
    public function setPlugin(?string $plugin): RouteDecorator
    {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * @return string
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param $controller
     * @return $this
     */
    public function setController(string $controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param $action
     * @return $this
     */
    public function setAction($action): RouteDecorator
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     * @return $this
     */
    public function setMethods(array $methods): RouteDecorator
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate() : ?string
    {
        return $this->template;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate(string $template): RouteDecorator
    {
        $this->template = $template;
        return $this;
    }
}