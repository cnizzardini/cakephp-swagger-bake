<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class HalJson extends AbstractMediaType implements MediaTypeInterface
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
     * @inheritDoc
     */
    public function buildSchema(string $schemaType): Schema
    {
        if (!in_array($schemaType, ['array', 'object'])) {
            throw new \InvalidArgumentException(
                "Argument must be array or object but was given schema type `$schemaType`"
            );
        }

        return $schemaType === 'array' ? $this->collection() : $this->item();
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    protected function collection(): Schema
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
        return (new Schema())
            ->setAllOf([
                ['$ref' => self::HAL_ITEM],
                ['$ref' => $this->ref],
            ])
            ->setProperties([]);
    }
}
