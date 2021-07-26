<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\{Schema, SchemaProperty};

/**
 * Builds a generic sample response schema for XML and JSON
 *
 * For XML, just add $schema->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'));
 * @internal
 */
class Generic extends AbstractMediaType implements MediaTypeInterface
{
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
    protected function collection(): Schema
    {
        $openapi = $this->swagger->getArray();

        if (isset($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'])) {

            if ($this->schema) {
                $items = [
                    'type' => 'object',
                    'properties' => $this->schema->getProperties()
                ];
            }

            return (new Schema())
                ->setAllOf([
                    ['$ref' => '#/x-swagger-bake/components/schemas/Generic-Collection'],
                ])
                ->setProperties([
                    (new SchemaProperty())
                        ->setName($this->whichData($openapi))
                        ->setType('array')
                        ->setItems($items ?? [
                            'type' => 'object',
                            'allOf' => [
                                ['$ref' => $this->ref],
                            ],
                        ]),
                ]);
        }

        if ($this->schema) {
            $items = [
                'type' => 'object',
                'properties' => $this->schema->getProperties()
            ];
        }

        return (new Schema())
            ->setType('array')
            ->setItems($items ?? ['$ref' => $this->ref]);
    }

    /**
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    protected function item(): Schema
    {
        if ($this->schema) {
            return $this->schema;
        }

        return (new Schema())->setRefEntity($this->ref);
    }
}
