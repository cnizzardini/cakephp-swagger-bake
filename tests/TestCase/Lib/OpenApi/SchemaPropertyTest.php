<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;

class SchemaPropertyTest extends TestCase
{
    public function test_example_is_not_json_encoded_when_null(): void
    {
        $this->assertArrayNotHasKey('example', (new SchemaProperty())->setTitle('title')->toArray());
    }

    public function test_example_is_json_encoded_when_blank(): void
    {
        $property = (new SchemaProperty())->setTitle('title')->setExample('');
        $this->assertArrayHasKey('example', $property->toArray());
    }

    public function test_invalid_type_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SchemaProperty('test', 'invalid');
    }
}