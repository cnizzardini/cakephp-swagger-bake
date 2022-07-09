<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Model;

use Cake\Collection\Collection;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Table;
use MixerApi\Core\Model\Model;
use MixerApi\Core\Model\ModelFactory;
use MixerApi\Core\Utility\NamespaceUtility;
use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Route\RouteScanner;
use UnexpectedValueException;

/**
 * Finds all Entities associated with RESTful routes based on userland configurations
 */
class ModelScanner
{
    use LocatorAwareTrait;

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
     * Gets an array of ModelDecorator instances if the model is associated with a route and that route is visible.
     *
     * @return \SwaggerBake\Lib\Model\ModelDecorator[]
     * @throws \ReflectionException
     */
    public function getModelDecorators(): array
    {
        $return = [];

        $connection = ConnectionManager::get('default');
        $namespaces = $this->config->getNamespaces();

        foreach ($namespaces['tables'] as $tableNs) {
            $tables = NamespaceUtility::findClasses($tableNs . 'Model\Table');
            foreach ($tables as $table) {
                try {
                    if (!class_exists($table)) {
                        continue;
                    }
                    $class = (new \ReflectionClass($table))->getShortName();
                    if (str_ends_with($class, 'Table')) {
                        $class = substr($class, 0, strlen($class) - 5);
                    }
                    $tableInstance = $this->getTableLocator()->get($class);
                    $model = (new ModelFactory($connection, $tableInstance))->create();
                } catch (\Exception $e) {
                    continue;
                }

                $routeDecorator = $this->findRouteDecoratorByModel($model);
                if (!$this->isVisible($model, $routeDecorator)) {
                    continue;
                }

                if ($routeDecorator) {
                    $routeDecorator->setModel($model);
                    $controller = $routeDecorator->getControllerInstance();
                }

                $return[] = new ModelDecorator($model, $controller ?? null);
            }
        }

        return $return;
    }

    /**
     * Finds the RouteDecorator (if any) associated with the Model, otherwise returns null.
     *
     * @param \MixerApi\Core\Model\Model $model Model instance
     * @return \SwaggerBake\Lib\Route\RouteDecorator|null
     */
    private function findRouteDecoratorByModel(Model $model): ?RouteDecorator
    {
        $routes = $this->routeScanner->getRoutes();

        $result = (new Collection($routes))->filter(
            function (RouteDecorator $routeDecorator) use ($model) {
                return $this->routeHasModel($routeDecorator, $model);
            }
        );

        return $result->first();
    }

    /**
     * @return \SwaggerBake\Lib\Route\RouteScanner
     */
    public function getRouteScanner(): RouteScanner
    {
        return $this->routeScanner;
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

        return $schema->visibility != OpenApiSchema::VISIBLE_NEVER;
    }

    /**
     * Checks if the model is associated with the route in the following order:
     *
     * 1. Use LocatorAwareTrait::fetchTable (4.3 or higher) and
     * 2. See ModelScanner::routeHasModelFallback()
     *
     * @param \SwaggerBake\Lib\Route\RouteDecorator $routeDecorator RouteDecorator that will be checked
     * @param \MixerApi\Core\Model\Model $model Model that will be searched for in the RouteDecorator
     * @return bool
     * @throws \ReflectionException
     */
    private function routeHasModel(RouteDecorator $routeDecorator, Model $model): bool
    {
        try {
            $controller = $routeDecorator->getControllerInstance();
            if ($controller !== null && method_exists($controller, 'fetchTable')) {
                return $controller->fetchTable()->getAlias() == $model->getTable()->getAlias();
            }
        } catch (UnexpectedValueException $e) {
        }

        return $this->controllerHasModelFallback($routeDecorator, $model);
    }

    /**
     * Checks if the model is associated with the route in the following order:
     *
     * 1. CakePHP naming conventions
     * 2. Checking if the table alias exists in the controllers object var properties.
     *
     * @codeCoverageIgnore
     * @param \SwaggerBake\Lib\Route\RouteDecorator $routeDecorator RouteDecorator that will be checked
     * @param \MixerApi\Core\Model\Model $model Model that will be searched for in the RouteDecorator
     * @return bool
     * @throws \ReflectionException
     * @deprecated Consider for removal in future versions.
     */
    private function controllerHasModelFallback(RouteDecorator $routeDecorator, Model $model): bool
    {
        /*
         * Check using CakePHP naming conventions
         *
         * @todo: consider removing and only using fetchTable?
         */
        if ($routeDecorator->getController() === $model->getTable()->getAlias()) {
            return true;
        }

        /*
         * Check if the table alias exists in the controllers object var properties
         *
         * @todo: Can be removed when CakePHP 4.2 is no longer supported
         */
        $controller = $routeDecorator->getControllerInstance();
        if (is_null($controller)) {
            return false;
        }
        $results = (new Collection(get_object_vars($controller)))->filter(function ($item) {
            return $item instanceof Table;
        });

        return $results->count() > 0 && $results->first()->getAlias() === $model->getTable()->getAlias();
    }
}
