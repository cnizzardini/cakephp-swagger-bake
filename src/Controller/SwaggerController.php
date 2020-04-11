<?php
declare(strict_types=1);

namespace SwaggerBake\Controller;

use Cake\Core\Configure;
use SwaggerBake\Controller\AppController;

/**
 * Swagger Controller
 *
 *
 * @method \SwaggerBake\Model\Entity\Swagger[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SwaggerController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $title = 'SwaggerUI';
        $url = Configure::read('SwaggerBake.webPath');
        $this->set(compact('title','url'));
        return $this->render();
    }
}
