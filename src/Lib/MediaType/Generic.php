<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class Generic extends AbstractMediaType implements MediaTypeInterface
{
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
