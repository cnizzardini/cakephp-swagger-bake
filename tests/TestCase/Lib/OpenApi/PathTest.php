<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Path;

class PathTest extends TestCase
{
    public function test_get_set(): void
    {
        $path = new Path(
            resource: '/pet',
            operations: [new Operation('hello', 'get')],
            ref: $r = 'r',
            summary: $s = '1',
            description: $desc = '1',
        );

        $this->assertEquals($desc, $path->getDescription());
        $this->assertEquals($s, $path->getSummary());
        $this->assertEquals($r, $path->getRef());
        $this->assertInstanceOf(Operation::class, $path->getOperations()['get']);
    }
}