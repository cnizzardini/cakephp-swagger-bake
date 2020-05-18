<?php
declare(strict_types=1);

namespace SwaggerBake\Controller;

use App\Controller\AppController as BaseController;

class AppController extends BaseController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }
}
