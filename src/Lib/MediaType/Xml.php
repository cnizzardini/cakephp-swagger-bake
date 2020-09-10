<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\MediaType;

use SwaggerBake\Lib\OpenApi\Schema;

class Xml
{
    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema
     */
    private $schema;

    /**
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema instance of Schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
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
        return (new Schema())
            ->setAllOf([
                ['$ref' => $this->schema->getReadSchemaRef()],
            ])
            ->setXml((new \SwaggerBake\Lib\OpenApi\Xml())->setName('response'))
            ->setProperties([]);
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
