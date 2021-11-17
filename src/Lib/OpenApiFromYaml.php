<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Schema\SchemaFromYamlFactory;

class OpenApiFromYaml
{
    /**
     * Build the initial OpenAPI array from YAML.
     *
     * @param array $array OpenAPI array
     * @return array
     */
    public function build(array $array): array
    {
        if (!isset($array['paths'])) {
            $array['paths'] = [];
        }

        if (!isset($array['components']['schemas'])) {
            $array['components']['schemas'] = [];
        }

        $factory = new SchemaFromYamlFactory();

        foreach ($array['components']['schemas'] as $name => $var) {
            $schema = $factory->create($name, $var)->setRefPath("#/components/schemas/$name");
            $array['components']['schemas'][$name] = $schema;
        }

        return $array;
    }
}
