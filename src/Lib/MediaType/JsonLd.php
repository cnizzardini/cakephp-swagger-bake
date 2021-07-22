<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class JsonLd extends AbstractMediaType implements MediaTypeInterface
{
    /**
     * @var string
     */
    public const JSONLD_ITEM = '#/x-swagger-bake/components/schemas/JsonLd-Item';

    /**
     * @var string
     */
    public const JSONLD_COLLECTION = '#/x-swagger-bake/components/schemas/JsonLd-Collection';

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
    private function collection(): Schema
    {
        return (new Schema())
            ->setAllOf([
                ['$ref' => self::JSONLD_COLLECTION],
            ])
            ->setProperties([
                (new SchemaProperty())
                    ->setName('member')
                    ->setType('array')
                    ->setItems([
                        'type' => 'object',
                        'allOf' => [
                            ['$ref' => self::JSONLD_ITEM],
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
                ['$ref' => self::JSONLD_ITEM],
                ['$ref' => $this->ref],
            ])
            ->setProperties([]);
    }
}
