<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Model;

use Cake\Collection\Collection;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use MixerApi\Core\Model\Model;
use MixerApi\Core\Model\ModelFactory;
use MixerApi\Core\Utility\NamespaceUtility;
use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
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
     * @param \SwaggerBake\Lib\Route\RouteScanner $routeScanner RouteScanner
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(
        private RouteScanner $routeScanner,
        private Configuration $config
    ) {
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
     * The user-defined `prefix` from the swagger_bake config file.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->config->getPrefix();
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
        $reflection = new ReflectionClass(get_class($model->getEntity()));
        $schema = (new AttributeFactory($reflection, OpenApiSchema::class))->createOneOrNull();

        if (!$schema instanceof OpenApiSchema) {
            return $routeDecorator !== null;
        }

        return $schema->isVisible;
    }
}
