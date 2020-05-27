<?php

namespace SwaggerBake\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\Swagger;

/**
 * Class SwaggerUiComponent
 * @package SwaggerBake\Controller\Component
 */
class SwaggerUiComponent extends Component
{
    public $components = ['Flash'];

    /** @var Configuration */
    public $config;

    /** @var Swagger */
    public $swagger;

    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
        $this->config = new Configuration();
        $this->swagger = (new SwaggerFactory())->create();

    }

    public function beforeFilter(Event $event) : void
    {
        if ($this->config->getHotReload()) {
            $output = $this->config->getJson();
            $this->swagger->writeFile($output);
        }

        foreach ($this->swagger->getOperationsWithNoHttp20x() as $operation) {
            $errorMsg = 'Operation ' . $operation->getOperationId() . ' does not have a HTTP 20x response';
            if (!isset($this->Flash)) {
                triggerWarning($errorMsg);
                continue;
            }
            $this->Flash->error($errorMsg);
        }
    }

    /**
     * @return Configuration
     */
    public function getSwaggerBakeConfiguration() : Configuration
    {
        return $this->config;
    }
}