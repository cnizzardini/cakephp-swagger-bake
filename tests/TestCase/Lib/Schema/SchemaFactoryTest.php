<?php

namespace SwaggerBake\Test\TestCase\Lib\Schema;

use Cake\Controller\Controller;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use MixerApi\Core\Model\ModelFactory;
use SwaggerBake\Lib\Model\ModelDecorator;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Schema\SchemaFactory;
use SwaggerBakeTest\App\Model\Table\DepartmentsTable;

class SchemaFactoryTest extends TestCase
{
    public function test_create_schema(): void
    {
        $connection = ConnectionManager::get('default');
        $department = (new ModelFactory($connection, new DepartmentsTable()))->create();
        $decorator = new ModelDecorator($department, $controller = new Controller());
        $this->assertEquals($controller, $decorator->getController());
        $schema = (new SchemaFactory())->create($decorator);
        $this->assertInstanceOf(Schema::class, $schema);

        /** @var SchemaProperty[] $properties */
        $properties = $schema->getProperties();
        $this->assertEquals('this_is_a_unit_test_for_description', $properties['name']->getDescription());
    }

    public function test_write_schema(): void
    {
        $connection = ConnectionManager::get('default');
        $department = (new ModelFactory($connection, new DepartmentsTable()))->create();
        $decorator = new ModelDecorator($department, new Controller());
        $schema = (new SchemaFactory())->create($decorator, SchemaFactory::WRITEABLE_PROPERTIES);
        $this->assertCount(1, $schema->getProperties());
    }
}