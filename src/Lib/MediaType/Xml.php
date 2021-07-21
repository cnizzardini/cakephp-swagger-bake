<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class Xml extends AbstractMediaType
{
    /**
     * Returns Xml schema
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
                ->setAllOf([
                    ['$ref' => $this->ref],
                ])
                ->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'))
                ->setProperties([]);
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => '#/x-swagger-bake/components/schemas/Generic-Collection'],
            ])
            ->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'))
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
        return (new Schema())
            ->setAllOf([
                ['$ref' => $this->ref],
            ])
            ->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'))
            ->setProperties([]);
    }
}
