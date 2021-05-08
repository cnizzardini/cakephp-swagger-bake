<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\SchemaRefUtility;

class Generic
{
    use GenericTrait;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema
     */
    private $schema;

    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    private $swagger;

    /**
     * The OpenAPI $ref for the Schema
     *
     * @var string
     */
    private $ref;

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema instance of Schema
     * @param \SwaggerBake\Lib\Swagger $swagger instance of Swagger
     */
    public function __construct(Schema $schema, Swagger $swagger)
    {
        $this->schema = $schema;
        $this->swagger = $swagger;
        $this->ref = SchemaRefUtility::whichRef($schema, $swagger, $this->schema->getReadSchemaRef());
    }

    /**
     * Returns a generic schema
     *
     * @param string $action controller action (e.g. add, index, view, edit, delete)
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    public function buildSchema(string $action): Schema
    {
        if ($action == 'index') {
            return $this->collection();
        }

        return $this->item();
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function collection(): Schema
    {
        $openapi = $this->swagger->getArray();

        if (!isset($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'])) {
            return (new Schema())
                ->setType('array')
                ->setItems(['$ref' => $this->ref]);
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => '#/x-swagger-bake/components/schemas/Generic-Collection'],
            ])
            ->setProperties([
                (new SchemaProperty())
                    ->setName($this->whichData($openapi))
                    ->setType('array')
                    ->setItems([
                        'type' => 'object',
                        'allOf' => [
                            ['$ref' => $this->ref],
                        ],
                    ]),
            ]);
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function item(): Schema
    {
        return (new Schema())->setRefEntity($this->ref);
    }
}
