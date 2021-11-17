<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Model;

use Cake\Controller\Controller;
use MixerApi\Core\Model\Model;

class ModelDecorator
{
    /**
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @param \Cake\Controller\Controller|null $controller Controller instance
     */
    public function __construct(
        private Model $model,
        private ?Controller $controller
    ) {
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
