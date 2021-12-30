<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class SchemaTest extends TestCase
{
    public function test(): void
    {
        $schema = new Schema();
        $this->assertEquals(
            $title = 'title',
            $schema->setTitle($title)->getTitle()
        );
        $this->assertEquals(
            $prop = 'prop',
            $schema->setVendorProperty('x-p', $prop)->getVendorProperty('x-p')
        );
        $this->assertEquals(
            $required = ['field'],
            $schema->setRequired($required)->getRequired()
        );
        $this->assertTrue($schema->setIsPublic(true)->isPublic());
    }

    public function test_push_property(): void
    {
        $property = (new SchemaProperty())->setRequired(true)->setName('test');
        $schema = new Schema();
        $schema->pushProperty($property);
        $this->assertCount(1, $schema->getProperties());
        $this->assertCount(1, $schema->getRequired());

        $property->setRequired(false);
        $schema->pushProperty($property);
        $this->assertCount(0, $schema->getRequired());
    }
}