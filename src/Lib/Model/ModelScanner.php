<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Model;

use Cake\Collection\Collection;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use MixerApi\Core\Model\Model;
use MixerApi\Core\Model\ModelFactory;
use MixerApi\Core\Utility\NamespaceUtility;
use SwaggerBake\Lib\Annotation\SwagEntity;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Utility\AnnotationUtility;

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

        $namespaces = $this->config->getNamespaces();

        foreach ($namespaces['tables'] as $tableNs) {
            $tables = NamespaceUtility::findClasses($tableNs . 'Model\Table');

            foreach ($tables as $table) {
                try {
                    if (!class_exists($table)) {
                        continue;
                    }

                    $reflectionClass = new \ReflectionClass($table);
                    if (!$reflectionClass->isInstantiable() || !$reflectionClass->isSubclassOf(Table::class)) {
                        continue;
                    }
                    $model = (new ModelFactory($connection, new $table()))->create();
                } catch (\Exception $e) {
                    continue;
                }

                if ($model === null) {
                    continue;
                }

                $routeDecorator = $this->getRouteDecorator($model);
                if (!$this->hasVisibility($model, $routeDecorator)) {
                    continue;
                }

                if ($routeDecorator) {
                    $controllerFqn = $routeDecorator->getControllerFqn();
                    $controller = $controllerFqn ? new $controllerFqn() : null;
                }

                $return[] = new ModelDecorator($model, $controller ?? null);
            }
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

    /**
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @param \SwaggerBake\Lib\Route\RouteDecorator|null $routeDecorator RouteDecorator instance
     * @return bool
     */
    private function hasVisibility(Model $model, ?RouteDecorator $routeDecorator): bool
    {
        $annotations = AnnotationUtility::getClassAnnotationsFromFqns(get_class($model->getEntity()));

        $swagEntities = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagEntity;
        });

        if (empty($swagEntities)) {
            return $routeDecorator !== null;
        }

        $swagEntity = reset($swagEntities);

        return $swagEntity->isVisible;
    }
}
