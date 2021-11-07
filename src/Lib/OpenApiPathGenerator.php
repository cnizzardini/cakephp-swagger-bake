<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Operation\OperationFromRouteFactory;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Route\RouteScanner;

class OpenApiPathGenerator
{
    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\Route\RouteScanner $routeScanner Route Scanner
     * @param \SwaggerBake\Lib\Configuration $config Swagger Configuration
     */
    public function __construct(
        private Swagger $swagger,
        private RouteScanner $routeScanner,
        private Configuration $config
    ) {
    }

    /**
     * Builds paths from CakePHP routes.
     *
     * @param array $openapi The OpenAPI array
     * @return array
     */
    public function generate(array $openapi = []): array
    {
        $routes = $this->routeScanner->getRoutes();
        $operationFactory = new OperationFromRouteFactory($this->swagger);

        $ignorePaths = array_keys($openapi['paths']);

        foreach ($routes as $route) {
            $resource = $route->templateToOpenApiPath();

            $path = $openapi['paths'][$resource] ?? (new PathFromRouteFactory($route))->create();

            if (!$path instanceof Path || in_array($path->getResource(), $ignorePaths)) {
                continue;
            }

            if ($route->getAction() == 'edit') {
                $methods = $this->config->get('editActionMethods');
            } else {
                $methods = $route->getMethods();
            }

            foreach ($methods as $httpMethod) {
                $schema = $this->getSchemaFromRoute($route);

                $operation = $operationFactory->create($route, $httpMethod, $schema);

                if (!$operation instanceof Operation) {
                    continue;
                }

                $path->pushOperation($operation);
            }

            EventManager::instance()->dispatch(
                new Event('SwaggerBake.Path.created', $path)
            );

            if (!empty($path->getOperations())) {
                $openapi['paths'][$route->templateToOpenApiPath()] = $path;
            }
        }

        return $openapi;
    }

    /**
     * Gets the Schema associated with a Route
     *
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private function getSchemaFromRoute(RouteDecorator $route): ?Schema
    {
        if ($route->getModel()) {
            $table = $route->getModel()->getTable()->getAlias();
        } else {
            $controller = $route->getController();
            $table = preg_replace('/\s+/', '', $controller);
        }

        if (in_array(strtolower($route->getAction()), ['add','view','edit','index','delete'])) {
            return $this->swagger->getSchemaByName(Inflector::singularize($table));
        }

        return null;
    }
}
