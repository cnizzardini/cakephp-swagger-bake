<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Model;

use Cake\Collection\Collection;
use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use MixerApi\Core\Model\Model;
use MixerApi\Core\Model\ModelFactory;
use MixerApi\Core\Utility\NamespaceUtility;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Route\RouteScanner;

/**
 * Finds all Entities associated with RESTful routes based on userland configurations
 */
class ModelScanner
{
    /**
     * @var \SwaggerBake\Lib\Route\RouteScanner
     */
    private $routeScanner;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @param \SwaggerBake\Lib\Route\RouteScanner $routeScanner RouteScanner
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(RouteScanner $routeScanner, Configuration $config)
    {
        $this->routeScanner = $routeScanner;
        $this->prefix = $config->getPrefix();
        $this->config = $config;
    }

    /**
     * Gets an array of ModelDecorator instances
     *
     * @return \SwaggerBake\Lib\Model\ModelDecorator[]
     */
    public function getModelDecorators(): array
    {
        $return = [];

        $connection = ConnectionManager::get('default');

        if (!$connection instanceof Connection) {
            throw new SwaggerBakeRunTimeException('Unable to get Database Connection instance');
        }

        $tables = NamespaceUtility::findClasses(Configure::read('App.namespace') . '\Model\Table');

        foreach ($tables as $table) {
            try {
                $model = (new ModelFactory($connection, new $table()))->create();
            } catch (\Exception $e) {
                continue;
            }

            if ($model === null) {
                continue;
            }

            $routeDecorator = $this->getRouteDecorator($model);

            if ($routeDecorator) {
                $controllerFqn = $routeDecorator->getControllerFqn();
                $controller = $controllerFqn ? new $controllerFqn() : null;
            }

            $return[] = new ModelDecorator($model, $controller ?? null);
        }

        return $return;
    }

    /**
     * @return \SwaggerBake\Lib\Route\RouteScanner
     */
    public function getRouteScanner(): RouteScanner
    {
        return $this->routeScanner;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return \SwaggerBake\Lib\Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @return \SwaggerBake\Lib\Route\RouteDecorator
     */
    private function getRouteDecorator(Model $model): ?RouteDecorator
    {
        $routes = $this->routeScanner->getRoutes();

        $result = (new Collection($routes))->filter(function (RouteDecorator $route) use ($model) {
            return $route->getController() == $model->getTable()->getAlias();
        });

        return $result->first();
    }
}
