<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Schema;

class SchemaTest extends TestCase
{
    public function test_get_set(): void
    {
        $obj = (new Schema())
            ->setTitle($title = 'title')
            //->setRequired($req = ['test'])
            //->pushRequired('other')
            //->setVendorProperty('x-prop', 'value')
        ;

        $this->assertEquals($title, $obj->getTitle());
    }
}