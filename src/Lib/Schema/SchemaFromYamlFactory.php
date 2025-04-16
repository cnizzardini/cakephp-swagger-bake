<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Schema;

use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\Xml;

/**
 * Class SchemaFromYamlFactory
 *
 * @package SwaggerBake\Lib\Schema
 */
class SchemaFromYamlFactory
{
    /**
     * Create an instance of Schema from YAML
     *
     * @param string $name Name of the Schema (i.e. cake entity name)
     * @param array $yml The OpenApi Schema object as an array
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    public function create(string $name, array $yml): Schema
    {
        $schema = (new Schema())
            ->setName($name)
            ->setTitle($yml['title'] ?? null)
            ->setType($yml['type'] ?? '')
            ->setDescription($yml['description'] ?? null)
            ->setItems($yml['items'] ?? [])
            ->setAllOf($yml['allOf'] ?? [])
            ->setAnyOf($yml['anyOf'] ?? [])
            ->setOneOf($yml['oneOf'] ?? [])
            ->setNot($yml['oneOf'] ?? [])
            ->setExample($yml['example'] ?? null);

        if (isset($yml['xml'])) {
            $schema->setXml(
                (new Xml())
                    ->setName($yml['xml']['name'])
                    ->setAttribute($yml['xml']['attribute'] ?? null)
                    ->setNamespace($yml['xml']['namespace'] ?? null)
                    ->setPrefix($yml['xml']['prefix'] ?? null)
                    ->setWrapped($yml['xml']['wrapped'] ?? null),
            );
        }

        $factory = new SchemaPropertyFromYamlFactory();
        $yml['properties'] = $yml['properties'] ?? [];

        foreach ($yml['properties'] as $propertyName => $propertyVar) {
            /*
             * Property is a Schema
             */
            if (!empty($propertyVar['type']) && $propertyVar['type'] === 'object') {
                $schema->pushProperty(
                    $this->create($propertyName, $propertyVar),
                );
            /*
             * Property is a property
             */
            } else {
                $schema->pushProperty(
                    $factory->create($propertyName, $propertyVar),
                );
            }
        }

        return $schema;
    }
}
