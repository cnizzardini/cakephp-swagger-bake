<?php
declare(strict_types=1);

namespace SwaggerBake\Controller;

use Cake\Event\EventInterface;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;

/**
 * Swagger Controller
 *
 *
 * @method \SwaggerBake\Model\Entity\Swagger[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SwaggerController extends AppController
{
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $config = new Configuration();

        if ($config->getHotReload()) {
            $output = $config->getJson();
            $swagger = (new SwaggerFactory())->create();
            $swagger->writeFile($output);
        }
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $config = new Configuration();
        $title = 'SwaggerUI';
        $url = $config->getWebPath();
        $this->set(compact('title','url'));
        return $this->render();
    }
}
