<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Controller\Controller;
use Cake\Database\Exception\DatabaseException;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Locator\LocatorInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use MixerApi\Core\Model\ModelFactory;
use SwaggerBake\Lib\Attribute\OpenApiResponse;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ModelDecorator;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaInterface;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Schema\SchemaFactory;
use SwaggerBake\Lib\Swagger;

/**
 * Handles OpenApiResponse attributes containing the association option.
 */
class OperationResponseAssociation
{
    /**
     * @param \SwaggerBake\Lib\Swagger $swagger instance of Swagger
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route instance of RouteDecorator
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema null or an instance of Schema
     * @param \Cake\ORM\Locator\LocatorInterface|null $locator if null, a Locator will be created
     * @param \Cake\Utility\Inflector|null $inflector if null, an Inflector will be created
     */
    public function __construct(
        private Swagger $swagger,
        private RouteDecorator $route,
        private ?Schema $schema = null,
        private ?LocatorInterface $locator = null,
        private ?Inflector $inflector = null
    ) {
        $this->locator = $locator ?? TableRegistry::getTableLocator();
        $this->inflector = $inflector ?? new Inflector();
    }

    /**
     * Builds a new schema with associations
     *
     * @param \SwaggerBake\Lib\Attribute\OpenApiResponse $openApiResponse OpenApiResponse instance
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \ReflectionException
     */
    public function build(OpenApiResponse $openApiResponse): Schema
    {
        $associations = $openApiResponse->associations;

        if (!isset($associations['table'])) {
            $associations['table'] = $this->route->getController();
        }

        $table = $this->locator->get($associations['table']);
        $schema = $this->findSchema($table);
        $schema
            ->setAllOf([['$ref' => $schema->getRefPath()]])
            ->setRefPath(null)
            ->setProperties([]);

        if (!isset($associations['whiteList']) || !count($associations['whiteList'])) {
            $associations['whiteList'] = [];
            /** @var \Cake\ORM\Association $association */
            foreach ($table->associations() as $association) {
                $associations['whiteList'][] = $association->getAlias();
            }
        }

        foreach ($associations['whiteList'] as $item) {
            $schema = $this->associate($table, $schema, explode('.', $item));
        }

        return $schema;
    }

    /**
     * Recursively associates schemas.
     *
     * @param \Cake\ORM\Table $table The base table
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema The base schema
     * @param array $assoc An array of tables to be associated matching the order of the association tree.
     * @param array|null $current Passed by recursion. Holds the current depth in the association tree
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $baseSchema Passed by recursion. Holds the base schema.
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \ReflectionException
     */
    private function associate(
        Table $table,
        Schema $schema,
        array $assoc,
        ?array $current = null,
        ?Schema $baseSchema = null
    ): Schema {
        $current = $current ?? array_slice($assoc, 0, 1);
        $baseSchema = $baseSchema ?? $schema;
        $association = $table->getAssociation(implode('.', $current));
        $entity = $this->inflector::singularize($association->getAlias());
        $associatedSchema = $this->getOrCreateAssociatedSchema($entity, $association->getAlias());
        $associatedSchema
            ->setAllOf([['$ref' => $associatedSchema->getRefPath()]])
            ->setProperties([]);

        if ($associatedSchema->getRefPath()) {
            $associatedSchema->setRefPath(null);
        }

        if (count($current) != count($assoc)) {
            $current = array_slice($assoc, 0, count($current) + 1);
            $associatedSchema = $this->associate($table, $associatedSchema, $assoc, $current);
        }

        if ($association instanceof HasMany || $association instanceof BelongsToMany) {
            $baseSchema->pushProperty($this->associateMany($entity, $associatedSchema));
        } else {
            $baseSchema->pushProperty($this->associateOne($entity, $associatedSchema));
        }

        return $baseSchema;
    }

    /**
     * Gets or creates the Schema if it cannot be found. Returns a cloned instance.
     *
     * @param string $schemaName the schema name
     * @param string $tableName the table name (e.g. Actors, FilmActors)
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \ReflectionException
     */
    private function getOrCreateAssociatedSchema(string $schemaName, string $tableName): Schema
    {
        $schema = $this->swagger->getSchemaByName($schemaName);

        if (!$schema) {
            $assocTable = $this->locator->get($tableName);
            try {
                $model = (new ModelFactory(ConnectionManager::get('default'), $assocTable))->create();
            } catch (DatabaseException $e) {
                throw new SwaggerBakeRunTimeException('Error building association: ' . $e->getMessage());
            }

            $decorator = new ModelDecorator($model, new Controller());
            $schema = (new SchemaFactory())->createAlways($decorator, SchemaFactory::READABLE_PROPERTIES);
        }

        return clone $schema;
    }

    /**
     * Find the Schema and return a cloned instance.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return \SwaggerBake\Lib\OpenApi\Schema
     * @throws \ReflectionException
     */
    private function findSchema(Table $table): Schema
    {
        if ($this->schema instanceof Schema) {
            return clone $this->schema;
        }

        $entityName = $this->inflector::singularize($table->getAlias());

        $schema = $this->swagger->getSchemaByName($entityName);
        if ($schema) {
            return clone $schema;
        }

        return $this->getOrCreateAssociatedSchema($entityName, $table->getAlias());
    }

    /**
     * @param string $tableName name of the table
     * @param \SwaggerBake\Lib\OpenApi\Schema $assocSchema instance of Schema
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    private function associateMany(string $tableName, Schema $assocSchema): SchemaInterface
    {
        $schemaProperty = (new SchemaProperty())
            ->setName($this->inflector::tableize($tableName))
            ->setType('array');

        // use existing openapi ref paths when possible, otherwise add full schema
        if (!empty($assocSchema->getRefPath())) {
            return $schemaProperty->setItems([
                '$ref' => $assocSchema->getRefPath(),
            ]);
        }

        $items = [
            'type' => 'object',
            'properties' => $assocSchema->getProperties(),
        ];

        if ($assocSchema->getAllOf()) {
            $items['allOf'] = $assocSchema->getAllOf();
        }

        return $schemaProperty->setItems($items);
    }

    /**
     * @param string $assocEntityName name of the entity
     * @param \SwaggerBake\Lib\OpenApi\Schema $assocSchema instance of Schema
     * @return \SwaggerBake\Lib\OpenApi\SchemaInterface
     */
    private function associateOne(string $assocEntityName, Schema $assocSchema): SchemaInterface
    {
        if (!empty($assocSchema->getRefPath())) {
            return (new SchemaProperty())
                ->setName($this->inflector::underscore($assocEntityName))
                ->setType('object')
                ->setRefEntity($assocSchema->getRefPath());
        }

        return $assocSchema->setName($this->inflector::underscore($assocEntityName));
    }
}
