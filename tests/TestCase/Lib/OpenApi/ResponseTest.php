<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Response;

class ResponseTest extends TestCase
{
    public function test_get_set(): void
    {
        $obj = (new Response())
            ->setDescription($desc = 'desc')
            ->setContent([new Content()]);

        $this->assertEquals($desc, $obj->getDescription());
        $this->assertInstanceOf(Content::class, $obj->getContent()[0]);
    }
}