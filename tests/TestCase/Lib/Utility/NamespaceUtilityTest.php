<?php

namespace SwaggerBake\Test\TestCase\Lib\Utility;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Utility\NamespaceUtility;
use SwaggerBake\Test\TestCase\Helper\ConfigurationHelperTrait;

class NamespaceUtilityTest extends TestCase
{
    use ConfigurationHelperTrait;

    public function test_get_classes(): void
    {
        $this->assertNotEmpty(NamespaceUtility::getClasses(['\SwaggerBakeTest\App\\'],'Controller'));
    }

    public function test_get_entity_fqn(): void
    {
        $configuration = $this->createConfiguration([
            'namespaces' => [
                'entities' => ['\SwaggerBakeTest\App\\'],
            ]
        ]);
        $fqn = NamespaceUtility::getEntityFullyQualifiedNameSpace('Department', $configuration);
        $this->assertEquals('\SwaggerBakeTest\App\Model\Entity\Department', $fqn);
    }

    public function test_get_entity_fqn_result_is_null(): void
    {
        $configuration = $this->createConfiguration([
            'namespaces' => [
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ]);
        $fqn = NamespaceUtility::getEntityFullyQualifiedNameSpace('Nope', $configuration);
        $this->assertNull($fqn);
    }

    public function test_get_table_fqn(): void
    {
        $configuration = $this->createConfiguration([
            'namespaces' => [
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ]);
        $fqn = NamespaceUtility::getTableFullyQualifiedNameSpace('DepartmentsTable', $configuration);
        $this->assertEquals('\SwaggerBakeTest\App\Model\Table\DepartmentsTable', $fqn);
    }

    public function test_get_table_fqn_is_null(): void
    {
        $configuration = $this->createConfiguration([
            'namespaces' => [
                'tables' => ['\SwaggerBakeTest\App\\'],
            ]
        ]);
        $fqn = NamespaceUtility::getTableFullyQualifiedNameSpace('NopeTable', $configuration);
        $this->assertNull($fqn);
    }
}