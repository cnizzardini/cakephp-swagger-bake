<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\LocatorInterface;
use Cake\ORM\Association\{HasMany, BelongsToMany};
use Cake\ORM\{Table, TableRegistry};
use Cake\Utility\Inflector;
use MixerApi\Core\Model\ModelFactory;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ModelDecorator;
use SwaggerBake\Lib\OpenApi\{Schema, SchemaProperty};
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Schema\SchemaFactory;
use SwaggerBake\Lib\Swagger;

class OperationResponseAssociation
{
    private Swagger $swagger;

    private RouteDecorator $route;

    /**
     * @var Schema|null
     */
    private $schema;

    private LocatorInterface $locator;

    private Inflector $inflector;

    public function __construct(
        Swagger $swagger,
        RouteDecorator $route,
        ?Schema $schema = null,
        ?LocatorInterface $locator = null,
        ?Inflector $inflector = null
    ) {
        $this->swagger = $swagger;
        $this->route = $route;
        $this->schema = $schema;
        $this->locator = $locator ?? TableRegistry::getTableLocator();
        $this->inflector = $inflector ?? new Inflector();
    }

    /**
     * Builds a new schema with associations
     *
     * @param SwagResponseSchema $annotation an instance of SwagResponseSchema
     * @return Schema
     * @throws \ReflectionException
     */
    public function build(SwagResponseSchema $annotation): Schema
    {
        if (!isset($annotation->associations['table'])) {
            $annotation->associations['table'] = $this->route->getController();
        }

        if ($annotation->associations['depth'] <> 1) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'SwagResponseSchema association depth must be a positive integer, but only a depth of `1`' .
                    'is currently supported. Given depth of `%s`.',
                    $annotation->associations['depth']
                )
            );
        }

        $table = $this->locator->get($annotation->associations['table']);
        if (!$table instanceof Table) {
            throw new SwaggerBakeRunTimeException(
                sprintf(
                    'Unable to locate table `%s`. Manually specify base table with the table option',
                    $annotation->associations['table']
                )
            );
        }

        $schemaMode = $this->whichSchemaMode();

        $schema = $this->findSchema($table, $schemaMode);

        $hasWhiteList = isset($annotation->associations['whiteList']) && is_array($annotation->associations['whiteList']);
        /**
         * @todo support recursion
         */
        foreach ($table->associations() as $tableName => $association) {
            if ($hasWhiteList && !in_array($tableName, $annotation->associations['whiteList'])) {
                continue;
            }

            $assocEntityName = $this->inflector::singularize($tableName);
            $assocSchema = $this->buildAssociatedSchema("$assocEntityName-$schemaMode", $tableName);

            if ($association instanceof HasMany || $association instanceof BelongsToMany) {
                $schemaProperty = (new SchemaProperty())
                    ->setName($this->inflector::tableize($tableName))
                    ->setType('array');

                // use existing openapi ref paths when possible, otherwise add full schema
                if (!empty($assocSchema->getRefPath())) {
                    $schemaProperty->setItems([
                        '$ref' => $assocSchema->getRefPath()
                    ]);
                } else {
                    $schemaProperty->setItems([
                        'type' => 'object',
                        'properties' => $assocSchema->getProperties()
                    ]);
                }

                $schema->pushProperty($schemaProperty);
                continue;
            }

            // for HasOne and BelongsTo: use existing openapi ref paths when possible, otherwise add full schema
            if (!empty($assocSchema->getRefPath())) {
                $schema->pushProperty(
                    (new SchemaProperty())
                        ->setName($this->inflector::underscore($assocEntityName))
                        ->setType('object')
                        ->setRefEntity($assocSchema->getRefPath())
                );
            } else {
                $schema->pushProperty(
                    $assocSchema->setName($this->inflector::underscore($assocEntityName))
                );
            };
        }

        return $schema;
    }

    /**
     * Returns the schema mode (i.e. Add, Edit, Read) for an HTTP method. For example, POST returns Add, while GET
     * returns Read.
     *
     * @return string
     * @throws SwaggerBakeRunTimeException if HTTP method is unknown
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
     * @return Schema|null
     */
    private function buildAssociatedSchema(string $schemaName, string $tableName): Schema
    {
        if (!$schema = $this->swagger->getSchemaByName($schemaName)) {
            $schema = $this->swagger->getSchemaByName($schemaName);
        }

        if (!$schema) {
            $assocTable = $this->locator->get($tableName);
            $model = (new ModelFactory(ConnectionManager::get('default'), $assocTable))->create();
            $decorator = new ModelDecorator($model, new Controller());
            $schema = (new SchemaFactory())->createAlways($decorator, SchemaFactory::READABLE_PROPERTIES);
        }

        return $schema;
    }

    /**
     * @param Table $table Table instance
     * @param string $schemaMode the schema mode (i.e. Read, Add, Edit)
     * @return Schema
     */
    private function findSchema(Table $table, string $schemaMode): Schema
    {
        if ($this->schema instanceof Schema) {
            return clone $this->schema;
        }

        $entityName = $this->inflector::singularize($table->getAlias());

        if (!$schema = $this->swagger->getSchemaByName($entityName . '-' . $schemaMode)) {
            $schema = $this->buildAssociatedSchema($entityName, $table->getAlias());
        }

        return $schema;
    }
}