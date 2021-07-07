<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Xml;

class XmlTest extends TestCase
{
    public function test_get_set(): void
    {
        $xml = (new Xml())
            ->setName($name = 'name')
            ->setNamespace($ns = 'ns')
            ->setPrefix($prefix = 'p')
            ->setAttribute($attr = true)
            ->setWrapped($wrap = true);

        $this->assertEquals($name, $xml->getName());
        $this->assertEquals($ns, $xml->getNamespace());
        $this->assertEquals($prefix, $xml->getPrefix());
        $this->assertEquals($attr, $xml->getAttribute());
        $this->assertEquals($wrap, $xml->getWrapped());
    }

    public function test_remove_defaults(): void
    {
        $array = (new Xml())->toArray();
        $this->assertArrayNotHasKey('wrapped', $array);
        $this->assertArrayNotHasKey('attribute', $array);
    }
}