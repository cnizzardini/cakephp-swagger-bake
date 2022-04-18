<?php

namespace SwaggerBake\Test\TestCase\Lib\Schema;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Schema\SchemaPropertyFromYamlFactory;

class SchemaPropertyFromYamlFactoryTest extends TestCase
{
    public function test_setters(): void
    {
        $properties = [
            'type' => 'string',
            'minLength' => 1,
            'maxLength' => 10,
            'pattern' => '/\s/',
            'minItems' => 1,
            'maxItems' => 10,
            'uniqueItems' => true,
            'exclusiveMinimum' => true,
            'exclusiveMaximum' => true,
            'minProperties' => 1,
            'maxProperties' => 10,
        ];

        $schemaProperty = (new SchemaPropertyFromYamlFactory())->create('testing', $properties);
        $this->assertEquals($properties['minLength'], $schemaProperty->getMinLength());
        $this->assertEquals($properties['maxLength'], $schemaProperty->getMaxLength());
        $this->assertEquals($properties['pattern'], $schemaProperty->getPattern());
        $this->assertEquals($properties['minItems'], $schemaProperty->getMinItems());
        $this->assertEquals($properties['maxItems'], $schemaProperty->getMaxItems());
        $this->assertEquals($properties['uniqueItems'], $schemaProperty->isUniqueItems());
        $this->assertEquals($properties['exclusiveMinimum'], $schemaProperty->isExclusiveMinimum());
        $this->assertEquals($properties['exclusiveMaximum'], $schemaProperty->isExclusiveMaximum());
        $this->assertEquals($properties['minProperties'], $schemaProperty->getMinProperties());
        $this->assertEquals($properties['maxProperties'], $schemaProperty->getMaxProperties());
    }
}