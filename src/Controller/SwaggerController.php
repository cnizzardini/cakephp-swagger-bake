<?php
declare(strict_types=1);

namespace SwaggerBake\Controller;

use Cake\Event\EventInterface;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\Swagger;

class SwaggerController extends AppController
{
    /** @var Swagger */
    private $swagger;

    /**
     * @see https://book.cakephp.org/4/en/controllers.html#controller-callback-methods
     * @param EventInterface $event
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $config = new Configuration();

        if ($config->getHotReload()) {
            $output = $config->getJson();
            $this->swagger = (new SwaggerFactory())->create();
            $this->swagger->writeFile($output);
        }
    }

    /**
     * Controller action for displaying built-in Swagger UI
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        foreach ($this->swagger->getOperationsWithNoHttp20x() as $operation) {
            if (!isset($this->Flash)) {
                triggerWarning('Operation ' . $operation->getOperationId() . ' does not have a HTTP 20x response');
                continue;
            }
            $this->Flash->error('Operation ' . $operation->getOperationId() . ' does not have a HTTP 20x response');
        }

        $config = new Configuration();
        $title = $config->getTitleFromYml();
        $url = $config->getWebPath();
        $this->set(compact('title','url'));
        $doctype = $this->request->getQuery('doctype');
        $this->viewBuilder()->setLayout($config->getLayout($doctype));
        return $this->render($config->getView($doctype));
    }
}
