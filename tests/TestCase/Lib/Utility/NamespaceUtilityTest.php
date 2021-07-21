<?php

namespace SwaggerBake\Test\TestCase\Lib\Utility;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\NamespaceUtility;

class NamespaceUtilityTest extends TestCase
{
    public function test_get_classes(): void
    {
        $this->assertNotEmpty(NamespaceUtility::getClasses(['\SwaggerBakeTest\App\\'],'Controller'));
    }

    public function test_get_entity_fqn(): void
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

    public function test_get_entity_fqn_result_is_null(): void
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

    public function test_get_table_fqn(): void
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

    public function test_get_table_fqn_is_null(): void
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