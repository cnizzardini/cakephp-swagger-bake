<?php

namespace SwaggerBake\Test\TestCase\Lib\Schema;

use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use MixerApi\Core\Model\ModelProperty;
use SwaggerBake\Lib\Schema\SchemaPropertyFactory;

class SchemaPropertyFactoryTest extends TestCase
{
    /**
     * When a column is a CakePHP/MySQL type of `json`, then it is created as oneOf: object or array in OpenAPI
     */
    public function test_json_field_type_is_openapi_oneof(): void
    {
        $property = (new SchemaPropertyFactory(new Validator()))->create(
            (new ModelProperty())->setName('json_field')->setType('json')
        );

        $this->assertNull($property->getType());
        $this->assertEquals([['type' => 'object'], ['type' => 'array', 'items' => []]], $property->getOneOf());
    }
}