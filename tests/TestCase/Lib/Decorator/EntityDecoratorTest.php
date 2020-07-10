<?php

namespace SwaggerBake\Test\TestCase\Lib\Utility;

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Decorator\EntityDecorator;
use SwaggerBakeTest\App\Model\Entity\Department;

class EntityDecoratorTest extends TestCase
{
    public function testGetSet()
    {
        $properties = [
            'entity' => new Department(),
            'name' => 'Department',
            'fqns' => 'SwaggerBakeTest\App\Model\Entity\Department'
        ];

        $entityDecorator = (new EntityDecorator(new Entity()))
            ->setName($properties['name'])
            ->setEntity($properties['entity'])
            ->setFqns($properties['fqns']);

        $this->assertEquals($properties['entity'], $entityDecorator->getEntity());
        $this->assertEquals($properties['name'], $entityDecorator->getName());
        $this->assertEquals($properties['fqns'], $entityDecorator->getFqns());
    }
}