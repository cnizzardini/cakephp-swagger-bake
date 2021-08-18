<?php

namespace SwaggerBake\Test\TestCase\Lib\Annotations;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagQuery;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

class AbstractParameterTest extends TestCase
{
    public function test_construct_invalid_argument_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SwagQuery(['']);
    }

    public function test_construct_invalid_type_results_in_swagger_bake_run_time_exception(): void
    {
        $this->expectException(SwaggerBakeRunTimeException::class);
        new SwagQuery(['name' => 'test', 'ref' => 'test', 'type' => 'nope']);
    }

    public function test_construct_invalid_example_results_in_swagger_bake_run_time_exception(): void
    {
        $this->expectException(SwaggerBakeRunTimeException::class);
        new SwagQuery(['name' => 'test', 'ref' => 'test', 'type' => 'string', 'example' => []]);
    }
}