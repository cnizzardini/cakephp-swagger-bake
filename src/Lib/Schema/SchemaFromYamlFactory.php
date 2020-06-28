<?php

namespace SwaggerBake\Lib\Schema;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\Xml;

/**
 * Class SchemaFromYamlFactory
 * @package SwaggerBake\Lib\Schema
 */
class SchemaFromYamlFactory
{
    /**
     * Create an instance of Schema from YAML
     *
     * @param string $name
     * @param array $yml
     * @return Schema
     */
    public function create(string $name, array $yml) : Schema
    {
        $schema = (new Schema())
            ->setName($name)
            ->setTitle($yml['title'] ?? '')
            ->setType($yml['type'] ?? '')
            ->setDescription($yml['description'] ?? '')
            ->setItems($yml['items'] ?? [])
            ->setAllOf($yml['allOf'] ?? [])
            ->setAnyOf($yml['anyOf'] ?? [])
            ->setOneOf($yml['oneOf'] ?? [])
            ->setNot($yml['oneOf'] ?? [])
        ;

        if (isset($yml['xml'])) {
            $schema->setXml(
                (new Xml())
                    ->setName($yml['xml']['name'])
                    ->setAttribute($yml['xml']['attribute'] ?? null)
                    ->setNamespace($yml['xml']['namespace'] ?? null)
                    ->setPrefix($yml['xml']['prefix'] ?? null)
                    ->setWrapped($yml['xml']['wrapped'] ?? null)
            );
        }

        $factory = new SchemaPropertyFromYamlFactory();
        $yml['properties'] = $yml['properties'] ?? [];

        foreach ($yml['properties'] as $propertyName => $propertyVar) {
            $schema->pushProperty(
                $factory->create($propertyName, $propertyVar)
            );
        }

        return $schema;
    }
}