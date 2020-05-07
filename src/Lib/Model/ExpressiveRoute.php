<?php

namespace SwaggerBake\Lib\Model;

use Cake\Routing\Route\Route;

class ExpressiveRoute
{
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
    public function setName(string $name): ExpressiveRoute
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
     * @param $plugin
     * @return $this
     */
    public function setPlugin(string $plugin): ExpressiveRoute
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
    public function setAction($action): ExpressiveRoute
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
    public function setMethods(array $methods): ExpressiveRoute
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
    public function setTemplate(string $template): ExpressiveRoute
    {
        $this->template = $template;
        return $this;
    }
}