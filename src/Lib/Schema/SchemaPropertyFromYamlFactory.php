<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use SwaggerBake\Lib\OpenApi\SchemaProperty;

/**
 * Class SchemaPropertyFromYamlFactory
 *
 * @package SwaggerBake\Lib\Schema
 */
class SchemaPropertyFromYamlFactory
{
    /**
     * Creates an instance of SchemaProperty from YAML
     *
     * @param string $name Name of the Property (i.e. the column name from the database table)
     * @param array $yaml OpenApi YAML for the property as an array
     * @return \SwaggerBake\Lib\OpenApi\SchemaProperty
     */
    public function create(string $name, array $yaml): SchemaProperty
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
            ->setItems($yaml['items'] ?? [])
            ->setRefEntity($yaml['$ref'] ?? '')
            ->setFormat($yaml['format'] ?? '');

        $properties = [
            'minLength',
            'maxLength',
            'pattern',
            'minItems',
            'maxItems',
            'uniqueItems',
            'exclusiveMinimum',
            'exclusiveMaximum',
            'minProperties',
            'maxProperties',
        ];

        foreach ($properties as $property) {
            $setterMethod = 'set' . ucfirst($property);
            if (!isset($yaml[$property]) || !method_exists(SchemaProperty::class, $setterMethod)) {
                continue;
            }
            $schemaProperty->{$setterMethod}($yaml[$property]);
        }

        return $schemaProperty;
    }
}
