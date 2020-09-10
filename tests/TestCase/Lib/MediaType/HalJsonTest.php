<?php

namespace SwaggerBake\Test\TestCase\Lib\MediaType;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\OpenApi\Schema;

class HalJsonTest extends TestCase
{
    public function testCollection()
    {
        $schema = (new HalJson((new Schema())->setName('Test')))->buildSchema('index');

        $this->assertEquals(HalJson::HAL_COLLECTION, $schema->getAllOf()[0]['$ref']);
        $this->assertEquals(
            HalJson::HAL_ITEM,
            $schema->getProperties()['_embedded']->getItems()['allOf'][0]['$ref']
        );
        $this->assertEquals(
            Schema::SCHEMA . 'Test-Read',
            $schema->getProperties()['_embedded']->getItems()['allOf'][1]['$ref']
        );
    }

    public function testItem()
    {
        $schema = (new HalJson((new Schema())->setName('Test')))->buildSchema('view');

        $this->assertEquals(
            HalJson::HAL_ITEM,
            $schema->getAllOf()[0]['$ref']
        );
        $this->assertEquals(
            Schema::SCHEMA . 'Test-Read',
            $schema->getAllOf()[1]['$ref']
        );
    }
}