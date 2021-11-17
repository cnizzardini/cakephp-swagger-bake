<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Model;

use Cake\Collection\Collection;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Table;
use Exception;
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
     * @throws \ReflectionException
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
                if (!$this->isVisible($model, $routeDecorator)) {
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
     * Returns the RouteDecorator associated with the Model using the loadedModel from the Controller.
     *
     * 1. Use CakePHP naming convention to determine the table.
     * 2. Check if the Controller has an instance of a \Cake\ORM\Table and match on the table alias.
     *
     * If neither options find a matching table for the controller then return null.
     *
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @return \SwaggerBake\Lib\Route\RouteDecorator
     */
    private function getRouteDecorator(Model $model): ?RouteDecorator
    {
        $routes = $this->routeScanner->getRoutes();

        $result = (new Collection($routes))->filter(function (RouteDecorator $route) use ($model) {
            if ($route->getController() == $model->getTable()->getAlias()) {
                $route->setModel($model);

                return true;
            } elseif ($route->getControllerFqn()) {
                $fqn = $route->getControllerFqn();
                try {
                    $results = (new Collection(get_object_vars(new $fqn())))->filter(function ($item) {
                        return $item instanceof Table;
                    });
                    if ($results->count() > 0) {
                        /** @var \Cake\ORM\Table $table */
                        $table = $results->first();
                        if ($table->getAlias() == $model->getTable()->getAlias()) {
                            $route->setModel($model);

                            return true;
                        }
                    }
                } catch (Exception $e) {
                }
            }
        });

        return $result->first();
    }

    /**
     * Checks OpenApiSchema attribute to determine if this model visible to OpenAPI.
     *
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @param \SwaggerBake\Lib\Route\RouteDecorator|null $routeDecorator RouteDecorator instance
     * @return bool
     * @throws \ReflectionException
     */
    private function isVisible(Model $model, ?RouteDecorator $routeDecorator): bool
    {
        $reflection = new ReflectionClass(get_class($model->getEntity()));
        $schema = (new AttributeFactory($reflection, OpenApiSchema::class))->createOneOrNull();

        if (!$schema instanceof OpenApiSchema) {
            return $routeDecorator !== null;
        }

        return $schema->visibility != OpenApiSchema::VISIBILE_NEVER;
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
}
