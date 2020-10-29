<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Swagger;

class Xml
{
    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema
     */
    private $schema;

    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    private $swagger;

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema instance of Schema
     * @param \SwaggerBake\Lib\Swagger $swagger instance of Swaggger
     */
    public function __construct(Schema $schema, Swagger $swagger)
    {
        $this->schema = $schema;
        $this->swagger = $swagger;
    }

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
                    ['$ref' => $this->schema->getReadSchemaRef()],
                ])
                ->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'))
                ->setProperties([]);
        }

        $dataElements = array_filter(
            array_keys($openapi['x-swagger-bake']['components']['schemas']['Generic-Collection']['properties']),
            function ($property) {
                return strstr('x-data-', $property);
            }
        );

        if (count($dataElements) === 1) {
            $dataElement = reset($dataElements);
            $data = str_replace('x-data-', '', $dataElement);
        } else {
            $data = 'data';
        }

        return (new Schema())
            ->setAllOf([
                ['$ref' => '#/x-swagger-bake/components/schemas/Generic-Collection'],
            ])
            ->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'))
            ->setProperties([
                (new SchemaProperty())
                    ->setName($data)
                    ->setType('array')
                    ->setItems([
                        'type' => 'object',
                        'allOf' => [
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
                ['$ref' => $this->schema->getReadSchemaRef()],
            ])
            ->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'))
            ->setProperties([]);
    }
}
