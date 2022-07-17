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
        $yaml = Yaml::parseFile(CONFIG . 'test-cases.yml');
        $schema = (new SchemaFromYamlFactory())->create(
            'Place',
            $yaml['components']['schemas']['Place']
        );


        $properties = $schema->getProperties();
        $this->assertTrue($properties['id']->isReadOnly());

        /** @var Schema $attributes */
        $attributes = $properties['attributes'];
        $this->assertTrue(isset($attributes->getProperties()['yitzo_country_code']));

        /** @var Schema $relationships */
        $relationships = $properties['relationships'];
        $this->assertTrue(isset($relationships->getProperties()['description']));
    }

    public function test_array_list(): void
    {
        $yaml = Yaml::parseFile(CONFIG . 'test-cases.yml');
        $schema = (new SchemaFromYamlFactory())->create(
            'Year',
            $yaml['components']['schemas']['Year']
        );

        $this->assertEquals(['type' => 'integer'], $schema->getItems());
        $this->assertEquals([2022, 2021, 2020], $schema->getExample());
    }
}