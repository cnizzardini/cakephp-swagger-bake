<?php

namespace SwaggerBake\Test\TestCase\Lib\Route;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Route\RouteScanner;

class RouteScannerTest extends TestCase
{
    public function test_construct_throws_invalid_arg_exception_when_prefix_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RouteScanner(new Router(), new Configuration(['prefix' => '']));
    }
}