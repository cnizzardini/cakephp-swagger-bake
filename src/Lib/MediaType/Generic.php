<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Swagger;

/**
 * Builds a generic sample response schema for XML and JSON
 *
 * For XML, just add $schema->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'));
 *
 * @internal
 */
final class Generic implements MediaTypeInterface
{
    use MediaTypeTrait;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger an instance of Swagger
     */
    public function __construct(private Swagger $swagger)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildSchema($schema, string $schemaType): Schema
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
        $openapi = $this->swagger->getArray();

        if ($schema instanceof Schema) {
            $items = [
                'type' => 'object',
                'properties' => $schema->getProperties(),
            ];
        }

        if (isset($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'])) {
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
                                ['$ref' => $schema],
                            ],
                        ]),
                ]);
        }

        return (new Schema())
            ->setType('array')
            ->setItems($items ?? ['$ref' => $schema]);
    }

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema|string $schema instance of Schema or an OpenAPI $ref string
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function item(Schema|string $schema): Schema
    {
        if ($schema instanceof Schema) {
            return $schema;
        }

        return (new Schema())->setRefEntity($schema);
    }

    /**
     * Determines the name of the element that contains the collections items
     *
     * @param array $openapi openapi array
     * @return string
     */
    private function whichData(array $openapi): string
    {
        if (!isset($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'])) {
            return 'data';
        }

        if ($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'] instanceof Schema) {
            /** @var \SwaggerBake\Lib\OpenApi\Schema $schema */
            $schema = $openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'];
            $array = $schema->toArray();

            return $array['x-data-element'] ?? 'data';
        }

        if (is_array($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection'])) {
            return $openapi['x-swagger-bake']['components']['schemas']['Generic-Collection']['x-data-element'];
        }

        return 'data';
    }
}
