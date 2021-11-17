<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Parameter;

class ParameterTest extends TestCase
{
    public function test_get_set(): void
    {
        $parameter = new Parameter(
            in: $in = 'query',
            name: 'test',
            style: $style = 'test'
        );
        $this->assertEquals($in, $parameter->getIn());
        $this->assertEquals($style, $parameter->getStyle());
    }

    public function test_jsonSerialize_returns_ref(): void
    {
        $this->assertEquals(
            'string', (new Parameter('query','string'))->jsonSerialize()['$ref']
        );
    }

    public function test_setIn_throws_invalid_arg_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Parameter('nope', 'ref');
    }
}