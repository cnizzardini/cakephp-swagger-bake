<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class JsonSchemaTraitTest extends TestCase
{
    public function test_get_set(): void
    {
        $schemaProperty = (new SchemaProperty())
            ->setTitle($title = 'test')
            ->setDefault($default = 'test')
            ->setNullable($nullable = true)
            ->setDeprecated($deprecated = false)
            ->setMultipleOf($multipleOf = 5)
            ->setUniqueItems($uniqueItems = false)
            ->setMaxProperties($maxProperties = 5)
            ->setMinProperties($minProperties = 1);

        $this->assertEquals($title, $schemaProperty->getTitle());
        $this->assertEquals($default, $schemaProperty->getDefault());
        $this->assertEquals($nullable, $schemaProperty->isNullable());
        $this->assertEquals($deprecated, $schemaProperty->isDeprecated());
        $this->assertEquals($multipleOf, $schemaProperty->getMultipleOf());
        $this->assertEquals($uniqueItems, $schemaProperty->isUniqueItems());
        $this->assertEquals($maxProperties, $schemaProperty->getMaxProperties());
        $this->assertEquals($minProperties, $schemaProperty->getMinProperties());
    }
}