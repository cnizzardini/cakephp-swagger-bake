<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Response;

class ResponseTest extends TestCase
{
    public function test_get_set(): void
    {
        $response = new Response('200', $desc = 'desc', [new Content('application/json', '')]);
        $this->assertEquals($desc, $response->getDescription());
        $this->assertInstanceOf(Content::class, $response->getContent()[0]);
    }
}