<?php

namespace SwaggerBake\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use SwaggerBake\Lib\Decorator\PropertyDecorator;
use SwaggerBake\Lib\Schema\SchemaPropertyFactory;
use SwaggerBake\Lib\Schema\SchemaPropertyValidation;

class SchemaPropertyValidationTest extends TestCase
{
    public function testWithValidations()
    {
        $validator = (new Validator())
            ->requirePresence('test_field', true)
            ->regex('test_field', '/\D/')
            ->minLength('test_field',5)
            ->maxLength('test_field',10)
        ;

        $propertyDecorator = (new PropertyDecorator())->setName('test_field')->setType('integer');
        $schemaProperty = (new SchemaPropertyFactory($validator))->create($propertyDecorator);

        $schemaPropertyValidation = new SchemaPropertyValidation(
            $validator,
            $schemaProperty,
            $propertyDecorator
        );

        $schemaProperty = $schemaPropertyValidation->withValidations();

        $this->assertTrue($schemaProperty->isRequired());
        $this->assertFalse($schemaProperty->isRequirePresenceOnCreate());
        $this->assertFalse($schemaProperty->isRequirePresenceOnUpdate());
        $this->assertEquals('/\D/', $schemaProperty->getPattern());
        $this->assertEquals(5, $schemaProperty->getMinLength());
        $this->assertEquals(10, $schemaProperty->getMaxLength());
    }

    public function testWithValidationsMinAndMax()
    {
        $validator = (new Validator())
            ->greaterThanOrEqual('test_field', 1)
            ->lessThanOrEqual('test_field', 1000);

        $propertyDecorator = (new PropertyDecorator())->setName('test_field')->setType('integer');
        $schemaProperty = (new SchemaPropertyFactory($validator))->create($propertyDecorator);

        $schemaPropertyValidation = new SchemaPropertyValidation(
            $validator,
            $schemaProperty,
            $propertyDecorator
        );

        $schemaProperty = $schemaPropertyValidation->withValidations();

        $this->assertEquals(1, $schemaProperty->getMinimum());
        $this->assertEquals(1000, $schemaProperty->getMaximum());
    }

    public function testWithValidationsExclusiveMinAndMax()
    {
        $validator = (new Validator())
            ->greaterThan('test_field', 1)
            ->lessThan('test_field', 1000);

        $propertyDecorator = (new PropertyDecorator())->setName('test_field')->setType('integer');
        $schemaProperty = (new SchemaPropertyFactory($validator))->create($propertyDecorator);

        $schemaPropertyValidation = new SchemaPropertyValidation(
            $validator,
            $schemaProperty,
            $propertyDecorator
        );

        $schemaProperty = $schemaPropertyValidation->withValidations();

        $this->assertEquals(1, $schemaProperty->getMinimum());
        $this->assertTrue($schemaProperty->isExclusiveMinimum());
        $this->assertEquals(1000, $schemaProperty->getMaximum());
        $this->assertTrue($schemaProperty->isExclusiveMaximum());
    }

    public function testWithValidationsArrays()
    {
        $validator = (new Validator())
            ->inList('test_field', ['A','B','C'])
            ->hasAtLeast('test_field', 2)
            ->hasAtMost('test_field', 4);

        $propertyDecorator = (new PropertyDecorator())->setName('test_field');
        $schemaProperty = (new SchemaPropertyFactory($validator))->create($propertyDecorator);

        $schemaPropertyValidation = new SchemaPropertyValidation(
            $validator,
            $schemaProperty,
            $propertyDecorator
        );

        $schemaProperty = $schemaPropertyValidation->withValidations();

        $this->assertEquals(2, $schemaProperty->getMinItems());
        $this->assertEquals(4, $schemaProperty->getMaxItems());
        $this->assertCount(3, $schemaProperty->getEnum());
    }

    public function testWithValidationsRequirePresenceCreate()
    {
        $validator = (new Validator())
            ->requirePresence('test_field', 'create');

        $propertyDecorator = (new PropertyDecorator())->setName('test_field');
        $schemaProperty = (new SchemaPropertyFactory($validator))->create($propertyDecorator);

        $schemaPropertyValidation = new SchemaPropertyValidation(
            $validator,
            $schemaProperty,
            $propertyDecorator
        );

        $schemaProperty = $schemaPropertyValidation->withValidations();

        $this->assertTrue($schemaProperty->isRequirePresenceOnCreate());
        $this->assertFalse($schemaProperty->isRequired());
        $this->assertFalse($schemaProperty->isRequirePresenceOnUpdate());
    }

    public function testWithValidationsRequirePresenceUpdate()
    {
        $validator = (new Validator())
            ->requirePresence('test_field', 'update');

        $propertyDecorator = (new PropertyDecorator())->setName('test_field');
        $schemaProperty = (new SchemaPropertyFactory($validator))->create($propertyDecorator);

        $schemaPropertyValidation = new SchemaPropertyValidation(
            $validator,
            $schemaProperty,
            $propertyDecorator
        );

        $schemaProperty = $schemaPropertyValidation->withValidations();

        $this->assertTrue($schemaProperty->isRequirePresenceOnUpdate());
        $this->assertFalse($schemaProperty->isRequired());
        $this->assertFalse($schemaProperty->isRequirePresenceOnCreate());
    }

    public function testWithValidationsEmptyString()
    {
        $validators = [
            'notEmptyString' => (new Validator())->notEmptyString('test_field'),
            'notBlank' => (new Validator())->notBlank('test_field')
        ];

        foreach ($validators as $rule => $validator) {
            $propertyDecorator = (new PropertyDecorator())->setName('test_field')->setType('string');
            $schemaProperty = (new SchemaPropertyFactory($validator))->create($propertyDecorator);

            $schemaPropertyValidation = new SchemaPropertyValidation(
                $validator,
                $schemaProperty,
                $propertyDecorator
            );

            $schemaProperty = $schemaPropertyValidation->withValidations();
            $this->assertEquals(1, $schemaProperty->getMinLength(), $rule);
        }
    }
}