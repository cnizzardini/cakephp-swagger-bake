<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Database\Schema\TableSchema;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\Annotation\SwagEntity;
use SwaggerBake\Lib\Decorator\EntityDecorator;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\NamespaceUtility;

/**
 * Class CakeModel
 *
 * @package SwaggerBake\Lib
 */
class CakeModel
{
    /**
     * @var \SwaggerBake\Lib\CakeRoute
     */
    private $cakeRoute;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @param \SwaggerBake\Lib\CakeRoute $cakeRoute CakeRoute
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(CakeRoute $cakeRoute, Configuration $config)
    {
        $this->cakeRoute = $cakeRoute;
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
        $scanner = new TableScanner($connection);
        $tables = $scanner->listUnskipped();
        $collection = $connection->getSchemaCollection();
        $routes = $this->cakeRoute->getRoutes();
        $tabularRoutes = $this->getTablesFromRoutes($routes);

        foreach ($tables as $tableName) {
            $classShortName = Inflector::classify($tableName);
            $entityFqns = NamespaceUtility::getEntityFullyQualifiedNameSpace($classShortName, $this->config);

            if (empty($entityFqns)) {
                continue;
            }

            if (!in_array($tableName, $tabularRoutes) && !$this->entityHasVisibility($entityFqns)) {
                continue;
            }

            $entityInstance = new $entityFqns();
            $schema = $collection->describe($tableName);

            $properties = $this->getPropertyDecorators($entityInstance, $schema);

            $return[] = (new EntityDecorator($entityInstance))->setProperties($properties);
        }

        return $return;
    }

    /**
     * @return \SwaggerBake\Lib\CakeRoute
     */
    public function getCakeRoute(): CakeRoute
    {
        return $this->cakeRoute;
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
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator[] $routes An array of RouteDecorator objects
     * @return string[]
     */
    private function getTablesFromRoutes(array $routes): array
    {
        $return = [];
        foreach ($routes as $route) {
            if (empty($route->getController())) {
                continue;
            }
            $return[] = Inflector::underscore($route->getController());
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
