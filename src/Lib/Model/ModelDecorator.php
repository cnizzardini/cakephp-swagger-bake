<?php

namespace SwaggerBake\Lib\Model;

use Cake\Controller\Controller;
use MixerApi\Core\Model\Model;

class ModelDecorator
{
    /**
     * @var \MixerApi\Core\Model\Model
     */
    private $model;

    /**
     * @var \Cake\Controller\Controller|null
     */
    private $controller;

    /**
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @param \Cake\Controller\Controller|null $controller Controller instance
     */
    public function __construct(Model $model, ?Controller $controller)
    {
        $this->model = $model;
        $this->controller = $controller;
    }

    /**
     * @return \MixerApi\Core\Model\Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return \Cake\Controller\Controller|null
     */
    public function getController(): ?Controller
    {
        return $this->controller;
    }
}