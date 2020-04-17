<?php
declare(strict_types=1);

namespace SwaggerBake\Controller;

use Cake\Event\EventInterface;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;

class SwaggerController extends AppController
{
    /**
     * @link https://book.cakephp.org/4/en/controllers.html#controller-callback-methods
     * @param EventInterface $event
     */
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
     * Controller action for displaying built-in Swagger UI
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $config = new Configuration();
        $title = $config->getTitleFromYml();
        $url = $config->getWebPath();
        $this->set(compact('title','url'));
        $doctype = $this->request->getQuery('doctype');
        $this->viewBuilder()->setLayout($config->getLayout($doctype));
        return $this->render($config->getView($doctype));
    }
}
