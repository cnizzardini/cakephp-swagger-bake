<?php

namespace SwaggerBake\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use MixerApi\Core\Model\ModelProperty;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Schema\SchemaPropertyFormat;

class SchemaPropertyFormatTest extends TestCase
{
    public function testWithFormat()
    {
        // cakephp validator rule => scalar data type
        $rules = [
            'creditCard' => ['type' => 'string', 'format' => 'credit-card'],
            'date'  => ['type' => 'string', 'format' => 'date'],
            'dateTime' => ['type' => 'string', 'format' => 'date-time'],
            'email' => ['type' => 'string', 'format' => 'email'],
            'ipv4' => ['type' => 'string', 'format' => 'ipv4'],
            'ipv6' => ['type' => 'string', 'format' => 'ipv6'],
            'latitude' => ['type' => 'float', 'format' => 'latitude'],
            'longitude' => ['type' => 'float', 'format' => 'longitude'],
            'time' => ['type' => 'string', 'format' => 'time'],
            'url' => ['type' => 'string', 'format' => 'url'],
            'urlWithProtocol' => ['type'=>'string', 'format' => 'url'],
            'uuid' => ['type' => 'string', 'format' => 'uuid'],
        ];

        foreach ($rules as $rule => $vars) {

            $schemaPropertyFormat = new SchemaPropertyFormat(
                (new Validator())->{$rule}('test_field'),
                (new SchemaProperty())->setName('test_field')->setType($vars['type']),
                (new ModelProperty())->setName('test_field')->setType($vars['type'])
            );
            $schemaProperty = $schemaPropertyFormat->withFormat();
            $this->assertEquals(
                $vars['format'],
                $schemaProperty->getFormat(),
                'Failed checking for format `' . $vars['format'] . '` from rule `' . $rule . '`'
            );
        }
    }
}