<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Database\Connection;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\Annotation\SwagEntity;
use SwaggerBake\Lib\Decorator\EntityDecorator;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\NamespaceUtility;

/**
 * Finds all Entities associated with RESTful routes based on userland configurations
 */
class EntityScanner
{
    /**
     * @var \SwaggerBake\Lib\RouteScanner
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
     * @param \SwaggerBake\Lib\RouteScanner $routeScanner RouteScanner
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(RouteScanner $routeScanner, Configuration $config)
    {
        $this->routeScanner = $routeScanner;
        $this->prefix = $config->getPrefix();
        $this->config = $config;
    }

    /**
     * Gets an array of EntityDecorator
     *
     * @return \SwaggerBake\Lib\Decorator\EntityDecorator[]
     */
    public function getEntityDecorators(): array
    {
        $return = [];

        $connection = ConnectionManager::get('default');

        if (!$connection instanceof Connection) {
            throw new SwaggerBakeRunTimeException('Unable to get Database Connection instance');
        }

        $scanner = new TableScanner($connection);
        $tables = $scanner->listUnskipped();

        $collection = $connection->getSchemaCollection();
        $routes = $this->routeScanner->getRoutes();
        $tabularRoutes = $this->getTablesFromRoutes($routes, $tables);

        foreach ($tables as $tableName) {
            $classShortName = Inflector::classify(Inflector::tableize($tableName));
            $entityFqns = NamespaceUtility::getEntityFullyQualifiedNameSpace($classShortName, $this->config);

            if (empty($entityFqns)) {
                continue;
            }

            if (!in_array($tableName, $tabularRoutes) && !$this->entityHasVisibility($entityFqns)) {
                continue;
            }

            $entityInstance = new $entityFqns();
            $schema = $collection->describe($tableName);

            if (!$schema instanceof TableSchema) {
                throw new SwaggerBakeRunTimeException('Unable to get TableSchema instance');
            }

            $properties = $this->getPropertyDecorators($entityInstance, $schema);

            $return[] = (new EntityDecorator($entityInstance))->setProperties($properties);
        }

        return $return;
    }

    /**
     * @return \SwaggerBake\Lib\RouteScanner
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
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator[] $routes an array of RouteDecorator objects
     * @param string[] $tables a list of table names
     * @return string[]
     */
    private function getTablesFromRoutes(array $routes, array $tables): array
    {
        $return = [];

        foreach ($routes as $route) {
            if (empty($route->getController())) {
                continue;
            }

            $conventionalName = Inflector::underscore($route->getController());
            if (in_array($conventionalName, $tables)) {
                $return[] = $conventionalName;
                continue;
            }

            $singularName = Inflector::underscore(Inflector::singularize($route->getController()));
            if (in_array($singularName, $tables)) {
                $return[] = $singularName;
                continue;
            }
        }

        return array_unique($return);
    }

    /**
     * @param \Cake\Datasource\EntityInterface $entity EntityInterface
     * @param \Cake\Database\Schema\TableSchema $schema TableSchema
     * @return \SwaggerBake\Lib\Decorator\PropertyDecorator[]
     */
    private function getPropertyDecorators(EntityInterface $entity, TableSchema $schema): array
    {
        $return = [];

        $hiddenAttributes = $entity->getHidden();

        $columns = array_filter($schema->columns(), function ($column) use ($hiddenAttributes) {
            return !in_array($column, $hiddenAttributes) ? true : null;
        });

        foreach ($columns as $columnName) {
            $vars = $schema->__debugInfo();
            $default = $vars['columns'][$columnName]['default'] ?? '';

            $PropertyDecorator = new PropertyDecorator();
            $PropertyDecorator
                ->setName($columnName)
                ->setType($schema->getColumnType($columnName))
                ->setDefault($default)
                ->setIsPrimaryKey($this->isPrimaryKey($vars, $columnName));
            $return[] = $PropertyDecorator;
        }

        return $return;
    }

    /**
     * @param array $schemaDebugInfo Debug array from TableSchema
     * @param string $columnName Column name
     * @return bool
     */
    private function isPrimaryKey(array $schemaDebugInfo, string $columnName): bool
    {
        if (!isset($schemaDebugInfo['constraints']['primary']['columns'])) {
            return false;
        }

        return in_array($columnName, $schemaDebugInfo['constraints']['primary']['columns']);
    }

    /**
     * @param string $fqns Fully Qualified Namespace of the entity
     * @return bool
     */
    private function entityHasVisibility(string $fqns): bool
    {
        if (empty($fqns)) {
            return false;
        }

        $annotations = AnnotationUtility::getClassAnnotationsFromFqns($fqns);

        $swagEntities = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagEntity;
        });

        if (empty($swagEntities)) {
            return false;
        }

        $swagEntity = reset($swagEntities);

        return $swagEntity->isVisible;
    }
}
