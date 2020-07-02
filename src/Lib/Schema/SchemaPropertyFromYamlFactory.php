<?php

namespace SwaggerBake\Lib\Schema;

use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Class SchemaPropertyFromYamlFactory
 * @package SwaggerBake\Lib\Schema
 */
class SchemaPropertyFromYamlFactory
{
    /**
     * Creates an instance of SchemaProperty from YAML
     *
     * @param string $name
     * @param array $yaml
     * @return SchemaProperty
     */
    public function create(string $name, array $yaml) : SchemaProperty
    {
        $schemaProperty = (new SchemaProperty())
            ->setName($name)
            ->setDescription($yaml['description'] ?? '')
            ->setType($yaml['type'] ?? '')
            ->setReadOnly($yaml['readonly'] ?? false)
            ->setWriteOnly($yaml['writeOnly'] ?? false)
            ->setRequired($yaml['required'] ?? false)
            ->setEnum($yaml['enum'] ?? [])
            ->setExample($yaml['example'] ?? '')
        ;

        $properties = [
            'maxLength',
            'minLength',
            'pattern',
            'maxItems',
            'minItems',
            'uniqueItems',
            'maxProperties',
            'exclusiveMaximum',
            'exclusiveMinimum',
            'uniqueItems',
            'maxProperties',
            'minProperties',
        ];

        foreach ($properties as $property) {
            if (!isset($yaml[$property])) {
                continue;
            }
            $setterMethod = 'set' . ucfirst($property);
            $schemaProperty->{$setterMethod}($yaml[$property]);
        }

        return $schemaProperty;
    }
}