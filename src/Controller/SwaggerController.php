<?php
declare(strict_types=1);

namespace SwaggerBake\Controller;

use Cake\Controller\Controller;
use Cake\Http\Response;
use SwaggerBake\Lib\Service\OpenApiControllerService;

class SwaggerController extends Controller
{
    /**
     * Displays either Swagger UI or Redoc
     *
     * @param \SwaggerBake\Lib\Service\OpenApiControllerService $service Builds OpenAPI JSON and hot reloads if enabled
     * @return \Cake\Http\Response Renders view
     */
    public function index(OpenApiControllerService $service): Response
    {
        /*
         * Rebuild OpenAPI if hotReload is enabled
         */
        $service->build();

        /*
         * Set some view vars
         */
        $config = $service->getConfig();
        $title = $config->getTitleFromYml();
        $url = $config->getWebPath();
        $this->set(compact('title', 'url'));

        /*
         * Set layout to either swagger or redoc
         *
         * @see vendor/cnizzardini/cakephp-swagger-bake/templates/layout
         */
        $doctype = $service->getDocType($this->request);
        $this->viewBuilder()->setLayout($config->getLayout($doctype));

        /*
         * Render either the swagger or redoc view
         *
         * @see vendor/cnizzardini/cakephp-swagger-bake/templates/Swagger
         */
        return $this->render($config->getView($doctype));
    }
}
