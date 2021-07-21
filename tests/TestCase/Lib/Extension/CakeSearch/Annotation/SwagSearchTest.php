<?php

namespace SwaggerBake\Test\TestCase\Lib\Extension\CakeSearch\Annotation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch;

class SwagSearchTest extends TestCase
{
    public function test_construct_invalid_argument_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SwagSearch(['']);
    }
}