<?php

namespace SwaggerBake\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use LogicException;
use SwaggerBake\Lib\Configuration;

class ConfigurationTest extends TestCase
{
    public function test_get_variable_does_not_exist_logic_exception(): void
    {
        $this->expectException(LogicException::class);
        (new Configuration())->get('nope');
    }

    public function test_set_variable_does_not_exist_logic_exception(): void
    {
        $this->expectException(LogicException::class);
        (new Configuration())->set('nope', 'value');
    }

    public function test_get_doc_type(): void
    {
        $this->assertEquals('swagger', (new Configuration())->getDocType());
    }
}