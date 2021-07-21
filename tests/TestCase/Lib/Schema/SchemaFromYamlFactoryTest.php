<?php

namespace SwaggerBake\Test\TestCase\Lib\Schema;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Schema\SchemaFromYamlFactory;
use Symfony\Component\Yaml\Yaml;

class SchemaFromYamlFactoryTest extends TestCase
{
    public function test_nested_objects(): void
    {
        $yaml = Yaml::parseFile(CONFIG . 'openapi-with-nested-objects.yml');
        $schema = (new SchemaFromYamlFactory())->create(
            'Place',
            $yaml['components']['schemas']['Place']
        );

        /** @var Schema $attributes */
        $attributes = $schema->getProperties()['attributes'];
        $this->assertTrue(isset($attributes->getProperties()['yitzo_country_code']));

        /** @var Schema $relationships */
        $relationships = $schema->getProperties()['relationships'];
        $this->assertTrue(isset($relationships->getProperties()['description']));
    }
}