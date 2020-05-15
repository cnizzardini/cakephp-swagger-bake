<?php

namespace SwaggerBake\Lib;

use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Database\Schema\TableSchema;
use Cake\Utility\Inflector;
use SwaggerBake\Lib\Decorator\EntityDecorator;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

/**
 * Class CakeModel
 */
class CakeModel
{
    /** @var CakeRoute */
    private $cakeRoute;

    /** @var string */
    private $prefix;

    /** @var Configuration */
    private $config;

    public function __construct(CakeRoute $cakeRoute, Configuration $config)
    {
        $this->cakeRoute = $cakeRoute;
        $this->prefix = $config->getPrefix();
        $this->config = $config;
    }

    /**
     * Gets an array of EntityDecorator
     *
     * @return EntityDecorator[]
     */
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
            $entity = $this->getEntityFromNamespaces($className);

            if (empty($entity)) {
                continue;
            }

            $entityInstance = new $entity;
            $schema = $collection->describe($tableName);

            $properties = $this->getPropertyDecorators($entityInstance, $schema);

            $return[] = (new EntityDecorator($entityInstance))->setProperties($properties);
        }

        return $return;
    }

    /**
     * @return CakeRoute
     */
    public function getCakeRoute() : CakeRoute
    {
        return $this->cakeRoute;
    }

    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return $this->prefix;
    }

    /**
     * @return Configuration
     */
    public function getConfig() : Configuration
    {
        return $this->config;
    }

    /**
     * @param string $className
     * @return string|null
     */
    private function getEntityFromNamespaces(string $className) : ?string
    {
        $namespaces = $this->config->getNamespaces();

        if (!isset($namespaces['entities']) || !is_array($namespaces['entities'])) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.entities'
            );
        }

        foreach ($namespaces['entities'] as $namespace) {
            $entity = $namespace . 'Model\Entity\\' . $className;
            if (class_exists($entity, true)) {
                return $entity;
            }
        }

        return null;
    }

    /**
     * @param array $routes
     * @return string[]
     */
    private function getTablesFromRoutes(array $routes) : array
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
     * @param EntityInterface $entity
     * @param TableSchema $schema
     * @return PropertyDecorator[]
     */
    private function getPropertyDecorators(EntityInterface $entity, TableSchema $schema) : array
    {
        $return = [];

        $hiddenAttributes = $entity->getHidden();

        $columns = array_filter($schema->columns(), function ($column) use ($hiddenAttributes) {
            return !in_array($column, $hiddenAttributes) ? true : null;
        });

        foreach ($columns as $columnName) {

            $vars = $schema->__debugInfo();
            $default = isset($vars['columns'][$columnName]['default']) ? $vars['columns'][$columnName]['default'] : '';

            $PropertyDecorator = new PropertyDecorator();
            $PropertyDecorator
                ->setName($columnName)
                ->setType($schema->getColumnType($columnName))
                ->setDefault($default)
                ->setIsPrimaryKey($this->isPrimaryKey($vars, $columnName))
            ;
            $return[] = $PropertyDecorator;
        }

        return $return;
    }

    /**
     * @param array $schemaDebugInfo
     * @param string $columnName
     * @return bool
     */
    private function isPrimaryKey(array $schemaDebugInfo, string $columnName) : bool
    {
        if (!isset($schemaDebugInfo['constraints']['primary']['columns'])) {
            return false;
        }

        return in_array($columnName, $schemaDebugInfo['constraints']['primary']['columns']);
    }
}