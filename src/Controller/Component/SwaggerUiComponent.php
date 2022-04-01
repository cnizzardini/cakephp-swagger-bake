<?php
declare(strict_types=1);

namespace SwaggerBake\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;

/**
 * Class SwaggerUiComponent
 *
 * @package SwaggerBake\Controller\Component
 */
class SwaggerUiComponent extends Component
{
    public $components = ['Flash'];

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    public $config;

    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    public $swagger;

    /**
     * SwaggerUiComponent constructor.
     *
     * @param \Cake\Controller\ComponentRegistry $registry ComponentRegistry
     * @param array $config configurations
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
    }

    /**
     * Perform a hot reload if the option is enabled. Also displays some errors using the Flash Component if
     * the user has that enabled.
     *
     * @param \Cake\Event\Event $event Event
     * @return void
     */
    public function beforeFilter(Event $event): void
    {
        $this->config = new Configuration();
        $this->swagger = (new SwaggerFactory())->create();

        if ($this->config->isHotReload()) {
            $output = $this->config->getJson();
            $this->swagger->writeFile($output);
        }

        foreach ($this->swagger->getOperationsWithNoHttp20x() as $operation) {
            $errorMsg = 'Operation ' . $operation->getOperationId() . ' does not have a HTTP 20x response';
            if (!$this->_registry->has('Flash')) {
                triggerWarning($errorMsg);
                continue;
            }
            $this->_registry->getController()->Flash->error($errorMsg);
        }
    }

    /**
     * @return \SwaggerBake\Lib\Configuration
     */
    public function getSwaggerBakeConfiguration(): Configuration
    {
        return $this->config;
    }

    /**
     * Gets the doc type (Swagger or Redoc) requested by the user
     *
     * @param \Cake\Http\ServerRequest $request ServerRequest
     * @return string
     */
    public function getDocType(ServerRequest $request): string
    {
        if (empty($request->getQuery('doctype')) || !is_string($request->getQuery('doctype'))) {
            return 'swagger';
        }

        $docType = h(strtolower($request->getQuery('doctype')));

        return in_array($docType, ['swagger','redoc']) ? $docType : 'swagger';
    }
}
