<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Path;

class PathTest extends TestCase
{
    public function test_get_set(): void
    {
        $path = (new Path())
            ->setDescription($desc = '1')
            ->setSummary($s = '2')
            ->setRef($r = 'r');

        $this->assertEquals($desc, $path->getDescription());
        $this->assertEquals($s, $path->getSummary());
        $this->assertEquals($r, $path->getRef());
    }
}