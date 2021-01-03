<?php

namespace SwaggerBake\Test\TestCase\Lib\Utility;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\NamespaceUtility;

class NamespaceUtilityTest extends TestCase
{
    public function testGetClasses()
    {
        $this->assertNotEmpty(NamespaceUtility::getClasses(['\SwaggerBakeTest\App\\'],'Controller'));
    }

    public function testGetEntityFullyQualifiedNameSpace()
    {
        $fqns = NamespaceUtility::getEntityFullyQualifiedNameSpace(
            'Department',
            new Configuration([
                'namespaces' => [
                    'entities' => ['\SwaggerBakeTest\App\\'],
                ]
            ])
        );
        $this->assertEquals('\SwaggerBakeTest\App\Model\Entity\Department', $fqns);
    }

    public function testGetEntityFullyQualifiedNameSpaceNull()
    {
        $fqns = NamespaceUtility::getEntityFullyQualifiedNameSpace(
            'Nope',
            new Configuration([
                'namespaces' => [
                    'tables' => ['\SwaggerBakeTest\App\\'],
                ]
            ])
        );
        $this->assertNull($fqns);
    }

    public function testGetTableFullyQualifiedNameSpace()
    {
        $fqns = NamespaceUtility::getTableFullyQualifiedNameSpace(
            'DepartmentsTable',
            new Configuration([
                'namespaces' => [
                    'tables' => ['\SwaggerBakeTest\App\\'],
                ]
            ])
        );
        $this->assertEquals('\SwaggerBakeTest\App\Model\Table\DepartmentsTable', $fqns);
    }

    public function testGetTableFullyQualifiedNameSpaceNull()
    {
        $fqns = NamespaceUtility::getTableFullyQualifiedNameSpace(
            'NopeTable',
            new Configuration([
                'namespaces' => [
                    'tables' => ['\SwaggerBakeTest\App\\'],
                ]
            ])
        );
        $this->assertNull($fqns);
    }
}