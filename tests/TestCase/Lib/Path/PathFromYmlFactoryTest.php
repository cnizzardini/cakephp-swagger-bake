<?php

namespace SwaggerBake\Test\TestCase\Lib\Path;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\Path\PathFromYmlFactory;

class PathFromYmlFactoryTest extends TestCase
{
    public $fixtures = [
        'plugin.SwaggerBake.Employees',
    ];

    public function testCreatePath()
    {
        $path = (new PathFromYmlFactory())->create('/pets', [
            'summary' => 'pet summary',
            'description' => 'lorem ipsum description'
        ]);
        $this->assertInstanceOf(Path::class, $path);
        $this->assertEquals('/pets', $path->getResource());
    }
}