<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Builds sample response schema for JSON-LD
 *
 * @internal
 */
final class JsonLd implements MediaTypeInterface
{
    use MediaTypeTrait;

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
    public function buildSchema(Schema|string $schema, string $schemaType): Schema
    {
        $this->validateSchemaType($schemaType);

        return $schemaType === 'array' ? $this->collection($schema) : $this->item($schema);
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema instance of Schema or an OpenAPI $ref string
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function collection(Schema|string $schema): Schema
    {
        if ($schema instanceof Schema) {
            $items = array_merge_recursive([
                'allOf' => [
                    ['$ref' => self::JSONLD_ITEM],
                ],
                'type' => 'object',
                'properties' => $this->recursion($schema->getProperties()),
            ], $this->buildDiscriminators($schema));
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => self::JSONLD_COLLECTION],
            ])
            ->setProperties([
                (new SchemaProperty())
                    ->setName('member')
                    ->setType('array')
                    ->setItems($items ?? [
                        'type' => 'object',
                        'allOf' => [
                            ['$ref' => self::JSONLD_ITEM],
                            ['$ref' => $schema],
                        ],
                    ]),
            ]);
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema instance of Schema or an OpenAPI $ref string
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function item(Schema|string $schema): Schema
    {
        if ($schema instanceof Schema) {
            return (new Schema())
                ->setAllOf([
                    ['$ref' => self::JSONLD_ITEM],
                ])
                ->setItems([
                    'type' => 'object',
                    'properties' => $this->recursion($schema->getProperties()),
                ])
                ->setProperties([]);
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => self::JSONLD_ITEM],
                ['$ref' => $schema],
            ])
            ->setProperties([]);
    }

    /**
     * @todo this method needs to actually be recursive
     * @param \SwaggerBake\Lib\OpenApi\SchemaProperty[] $properties an array of SchemaProperty
     * @return array
     */
    private function recursion(array $properties): array
    {
        foreach ($properties as $key => $property) {
            // @phpstan-ignore-next-line
            if (!in_array($property->getType(), ['object','array'])) {
                continue;
            }

            unset($properties[$key]);
            // @phpstan-ignore-next-line
            $items = $property->getItems();
            $items['allOf'][] = ['$ref' => self::JSONLD_ITEM];
            // @phpstan-ignore-next-line
            if ($property->getRefEntity()) {
                // @phpstan-ignore-next-line
                $items['allOf'][] = ['$ref' => $property->getRefEntity()];
            }

            // @phpstan-ignore-next-line
            if ($property->getType() === 'object') {
                $properties[$key] = $items;
            } else {
                if (isset($items['$ref'])) {
                    $items['allOf'][] = ['$ref' => $items['$ref']];
                    unset($items['$ref']);
                }
                // @phpstan-ignore-next-line
                $properties[$key]['items'] = $items;
            }
        }

        return $properties;
    }
}
