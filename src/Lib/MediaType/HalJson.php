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
        $this->validateSchemaType($schemaType);

        return $schemaType === 'array' ? $this->collection() : $this->item();
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function collection(): Schema
    {
        if ($this->schema) {
            $items = [
                'allOf' => [
                    ['$ref' => self::HAL_ITEM],
                ],
                'type' => 'object',
                'properties' => $this->recursion($this->schema->getProperties()),
            ];
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
        if ($this->schema) {
            return (new Schema())
                ->setAllOf([
                    ['$ref' => self::HAL_ITEM],
                ])
                ->setItems([
                    'type' => 'object',
                    'properties' => $this->recursion($this->schema->getProperties()),
                ])
                ->setProperties([]);
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => self::HAL_ITEM],
                ['$ref' => $this->ref],
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
                $properties['_embedded']['type'] = 'object';
                $properties['_embedded']['properties'][$key] = $items;
            } else {
                if (isset($items['$ref'])) {
                    $items['allOf'][] = ['$ref' => $items['$ref']];
                    unset($items['$ref']);
                }
                $properties['_embedded']['items'] = $items;
            }
        }

        return $properties;
    }
}
