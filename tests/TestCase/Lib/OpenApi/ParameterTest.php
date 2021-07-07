<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Parameter;

class ParameterTest extends TestCase
{
    public function test_get_set(): void
    {
        $obj = (new Parameter())
            ->setIn($in = 'query')
            ->setStyle($style = 'test')
        ;

        $this->assertEquals($in, $obj->getIn());
        $this->assertEquals($style, $obj->getStyle());
    }

    public function test_toArray_logic_exception(): void
    {
        $this->expectException(\LogicException::class);
        (new Parameter())->toArray();
    }

    public function test_jsonSerialize_returns_ref(): void
    {
        $this->assertEquals(
            'string', (new Parameter())->setIn('query')->setRef('string')->jsonSerialize()['$ref']
        );
    }

    public function test_setIn_throws_invalid_arg_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Parameter())->setIn('nope');
    }
}