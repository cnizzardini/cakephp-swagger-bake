<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Builds sample response schema for HAL+JSON
 *
 * @internal
 */
final class HalJson implements MediaTypeInterface
{
    use MediaTypeTrait;

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
                    ['$ref' => self::HAL_ITEM],
                ],
                'type' => 'object',
                'properties' => $this->recursion($schema->getProperties()),
            ], $this->buildDiscriminators($schema));
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => self::HAL_COLLECTION],
            ])
            ->setProperties([
                (new SchemaProperty())
                    ->setName('_embedded')
                    ->setType('array')
                    ->setItems($items ?? [
                        'type' => 'object',
                        'allOf' => [
                            ['$ref' => self::HAL_ITEM],
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
                    ['$ref' => self::HAL_ITEM],
                ])
                ->setItems([
                    'type' => 'object',
                    'properties' => $this->recursion($schema->getProperties()),
                ])
                ->setProperties([]);
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => self::HAL_ITEM],
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
            if (!in_array($property->getType(), ['object','array'])) {
                continue;
            }

            unset($properties[$key]);
            $items = $property->getItems();
            $items['allOf'][] = ['$ref' => self::HAL_ITEM];

            if ($property->getRefEntity()) {
                $items['allOf'][] = ['$ref' => $property->getRefEntity()];
            }

            if ($property->getType() === 'object') {
                // @phpstan-ignore-next-line
                $properties['_embedded']['type'] = 'object';
                $properties['_embedded']['properties'][$key] = $items;
            } else {
                if (isset($items['$ref'])) {
                    $items['allOf'][] = ['$ref' => $items['$ref']];
                    unset($items['$ref']);
                }
                // @phpstan-ignore-next-line
                $properties['_embedded']['items'] = $items;
            }
        }

        return $properties;
    }
}
