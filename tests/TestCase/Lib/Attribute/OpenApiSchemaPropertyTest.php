<?php

namespace SwaggerBake\Test\TestCase\Lib\Attribute;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;

class OpenApiSchemaPropertyTest extends TestCase
{
    /**
     * Specifying a type of array will always set the OpenAPI items property.
     *
     * @return void
     */
    public function test_type_array(): void
    {
        $property = (new OpenApiSchemaProperty(name: 'test', type: 'array'))->create();
        $this->assertEquals([], $property->getItems());

        $property = (new OpenApiSchemaProperty(name: 'test', type: 'array', items: ['type' => 'object']))->create();
        $this->assertEquals(['type' => 'object'], $property->getItems());
    }
}