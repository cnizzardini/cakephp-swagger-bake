<?php
declare(strict_types=1);

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use InvalidArgumentException;
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
        $this->expectException(InvalidArgumentException::class);
        new SchemaProperty('test', 'invalid');
    }

    public function test_enum_properties_is_indexed_numerically(): void
    {
        $vars = (new SchemaProperty())->setEnum(['test' => 'test'])->toArray();
        $this->assertArrayHasKey(0, $vars['enum']);
    }
}
