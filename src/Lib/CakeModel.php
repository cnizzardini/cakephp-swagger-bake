<?php


namespace SwaggerBake\Lib;


use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Database\Schema\TableSchema;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\Model\ExpressiveAttribute;
use SwaggerBake\Lib\Model\ExpressiveModel;

/**
 * Class CakeModel
 */
class CakeModel
{
    public function __construct(CakeRoute $cakeRoute, string $prefix, string $namespace = '\App\Model\Entity\\')
    {
        $this->cakeRoute = $cakeRoute;
        $this->prefix = $prefix;
        $this->namespace = $namespace;
    }

    public function getModels() : array
    {
        $return = [];

        $connection = ConnectionManager::get('default');
        $scanner = new TableScanner($connection);
        $tables = $scanner->listUnskipped();
        $collection = $connection->getSchemaCollection();
        $routes = $this->cakeRoute->getRoutes();
        $tabularRoutes = $this->getTablesFromRoutes($routes);

        foreach ($tables as $tableName) {

            if (!in_array($tableName, $tabularRoutes)) {
                continue;
            }

            $className = Inflector::classify($tableName);
            $entity = $this->namespace . $className;
            if (!class_exists($entity, true)) {
                continue;
            }

            $entityInstance = new $entity;
            $schema = $collection->describe($tableName);

            $attributes = $this->getExpressiveAttributes($entityInstance, $schema);

            $expressiveModel = new ExpressiveModel();
            $expressiveModel->setName($className)->setAttributes($attributes);

            $return[] = $expressiveModel;
        }

        return $return;
    }

    public function getCakeRoute() : CakeRoute
    {
        return $this->cakeRoute;
    }

    public function getPrefix() : string
    {
        return $this->prefix;
    }

    private function getTablesFromRoutes(array $routes) : array
    {
        $return = [];
        foreach ($routes as $route) {
            $controllerName = $this->cakeRoute->getControllerFromRoute($route);
            $return[] = Inflector::underscore($controllerName);
        }
        return array_unique($return);
    }

    private function getExpressiveAttributes(EntityInterface $entity, TableSchema $schema) : array
    {
        $return = [];

        $hiddenAttributes = $entity->getHidden();

        $columns = array_filter($schema->columns(), function ($column) use ($hiddenAttributes) {
            return !in_array($column, $hiddenAttributes) ? true : null;
        });

        foreach ($columns as $columnName) {

            $vars = $schema->__debugInfo();
            $default = isset($vars['columns'][$columnName]['default']) ? $vars['columns'][$columnName]['default'] : '';

            $expressiveAttribute = new ExpressiveAttribute();
            $expressiveAttribute
                ->setName($columnName)
                ->setType($schema->getColumnType($columnName))
                ->setDefault($default)
                ->setIsPrimaryKey($this->isPrimaryKey($vars, $columnName))
            ;
            $return[] = $expressiveAttribute;
        }

        return $return;
    }

    private function isPrimaryKey(array $schemaDebugInfo, string $columnName) : bool
    {
        if (!isset($schemaDebugInfo['constraints']['primary']['columns'])) {
            return false;
        }

        return in_array($columnName, $schemaDebugInfo['constraints']['primary']['columns']);
    }
}