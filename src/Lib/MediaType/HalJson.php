<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class HalJson
{
    /**
     * @var string
     */
    public const HAL_ITEM = '#/x-swagger-bake/components/schemas/HalJson-Item';

    /**
     * @var string
     */
    public const HAL_COLLECTION = '#/x-swagger-bake/components/schemas/HalJson-Collection';

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema
     */
    private $schema;

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema instance of Schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns HAL+JSON schema
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
        return (new Schema())
            ->setAllOf([
                ['$ref' => self::HAL_COLLECTION],
            ])
            ->setProperties([
                (new SchemaProperty())
                    ->setName('_embedded')
                    ->setType('array')
                    ->setItems([
                        'type' => 'object',
                        'allOf' => [
                            ['$ref' => self::HAL_ITEM],
                            ['$ref' => $this->schema->getReadSchemaRef()],
                        ],
                    ]),
            ]);
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function item(): Schema
    {
        return (new Schema())
            ->setAllOf([
                ['$ref' => self::HAL_ITEM],
                ['$ref' => $this->schema->getReadSchemaRef()],
            ])
            ->setProperties([]);
    }
}
