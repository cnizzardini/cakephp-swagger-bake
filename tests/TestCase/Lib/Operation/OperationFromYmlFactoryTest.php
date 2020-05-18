<?php

namespace SwaggerBake\Test\TestCase\Lib\Path;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationFromYmlFactory;

class OperationFromYmlFactoryTest extends TestCase
{
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    public function testCreatePath()
    {
        $operation = (new OperationFromYmlFactory())->create('GET', [
            'tags' => ['hello'],
            'operationId' => 'operation:id',
            'deprecated' => false
        ]);
        $this->assertInstanceOf(Operation::class, $operation);
    }
}