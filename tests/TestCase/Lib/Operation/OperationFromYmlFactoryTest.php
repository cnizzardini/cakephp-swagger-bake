<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\PathSecurity;
use SwaggerBake\Lib\Operation\OperationFromYmlFactory;
use SwaggerBake\Lib\Operation\OperationSecurity;

class OperationFromYmlFactoryTest extends TestCase
{
    public array $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    public function test_create_path(): void
    {
        $operation = (new OperationFromYmlFactory())->create('GET', [
            'tags' => ['hello'],
            'operationId' => 'operation:id',
            'deprecated' => false,
            'externalDocs' => [
                'url' => $url = 'https://github.com/cnizzardini/cakephp-swagger-bake',
                'description' => $desc = 'desc...'
            ],
            'security' => [
                'name' => ['scopes']
            ]
        ]);

        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals($url, $operation->getExternalDocs()->getUrl());
        $this->assertEquals($desc, $operation->getExternalDocs()->getDescription());
        $this->assertInstanceOf(PathSecurity::class, $operation->getSecurity()['name']);
    }
}