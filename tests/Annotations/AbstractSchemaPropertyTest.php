<?php

namespace SwaggerBake\Test\TestCase\Lib\Annotations;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagDtoRequestBody;

class AbstractSchemaPropertyTest extends TestCase
{
    public function test_construct_invalid_argument_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SwagDtoRequestBody(['']);
    }
}