<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\RequestBody;

class RequestBodyTest extends TestCase
{
    public function test_get_set(): void
    {
        $obj = (new RequestBody())
            ->setDescription($desc = 'desc')
            ->setRequired($req = true)
            ->setIgnoreCakeSchema($ig = true);

        $this->assertEquals($desc, $obj->getDescription());
        $this->assertEquals($req, $obj->isRequired());
        $this->assertEquals($ig, $obj->isIgnoreCakeSchema());
    }
}