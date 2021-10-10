<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Controller\Controller;
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

        if ($associations['depth'] <> 1) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'SwagResponseSchema association depth must be a positive integer, but only a depth of `1` ' .
                    'is currently supported. Given depth of `%s`.',
                    $associations['depth']
                )
            );
        }

        $table = $this->locator->get($associations['table']);
        if (!$table instanceof Table) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'Unable to locate table `%s`. Manually specify base table with the table option',
                    $associations['table']
                )
            );
        }

        $schemaMode = $this->whichSchemaMode();

        $schema = $this->findSchema($table, $schemaMode);

        $hasWhiteList = isset($associations['whiteList']) && is_array($associations['whiteList']);

        /**
         * @todo support recursion
         */
        foreach ($table->associations() as $tableName => $association) {
            if ($hasWhiteList && !in_array($tableName, $associations['whiteList'])) {
                continue;
            }

            $assocEntityName = $this->inflector::singularize($tableName);
            $assocSchema = $this->buildAssociatedSchema("$assocEntityName-$schemaMode", $tableName);

            if ($association instanceof HasMany || $association instanceof BelongsToMany) {
                $schema->pushProperty($this->associateMany($tableName, $assocSchema));
                continue;
            }

            $schema->pushProperty($this->associateOne($assocEntityName, $assocSchema));
        }

        return $schema;
    }

    /**
     * Returns the schema mode (i.e. Add, Edit, Read) for an HTTP method. For example, POST returns Add, while GET
     * returns Read.
     *
     * @return string
     * @throws \SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException if HTTP method is unknown
     */
    private function whichSchemaMode(): string
    {
        $types = [
            'POST' => 'Add',
            'PUT' => 'Edit',
            'PATCH' => 'Edit',
            'GET' => 'Read',
            'DELETE' => 'Read',
        ];

        $schemaModes = array_filter(
            $types,
            function (string $method) {
                return in_array($method, $this->route->getMethods());
            },
            ARRAY_FILTER_USE_KEY
        );

        if (empty($schemaModes)) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'Could not find schema mode for HTTP `%s`, expected one of `%s` for the HTTP method.',
                    implode(', ', $this->route->getMethods()),
                    implode(', ', array_keys($types))
                )
            );
        }

        return reset($schemaModes);
    }

    /**
     * @param string $schemaName the schema name such as Model-Read (e.g. Actor-Read, Actor-Add, Actor-Edit)
     * @param string $tableName the table name (e.g. Actors, FilmActors)
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function buildAssociatedSchema(string $schemaName, string $tableName): Schema
    {
        $schema = $this->swagger->getSchemaByName($schemaName);

        if (!$schema) {
            $assocTable = $this->locator->get($tableName);
            $model = (new ModelFactory(ConnectionManager::get('default'), $assocTable))->create();
            $decorator = new ModelDecorator($model, new Controller());
            $schema = (new SchemaFactory())->createAlways($decorator, SchemaFactory::READABLE_PROPERTIES);
        }

        return $schema;
    }

    /**
     * @param \Cake\ORM\Table $table Table instance
     * @param string $schemaMode the schema mode (i.e. Read, Add, Edit)
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function findSchema(Table $table, string $schemaMode): Schema
    {
        if ($this->schema instanceof Schema) {
            return clone $this->schema;
        }

        $entityName = $this->inflector::singularize($table->getAlias());

        return $this->swagger->getSchemaByName($entityName . '-' . $schemaMode) ??
            $this->buildAssociatedSchema($entityName, $table->getAlias());
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

        return $schemaProperty->setItems([
            'type' => 'object',
            'properties' => $assocSchema->getProperties(),
        ]);
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
