<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Response;

class ResponseTest extends TestCase
{
    public function test_get_set(): void
    {
        $response = new Response('200', '', [new Content('application/json', '')]);
        $this->assertInstanceOf(Content::class, $response->getContent()[0]);
        $this->assertEquals($code = '201', $response->setCode($code)->getCode());
        $this->assertEquals($desc = 'desc..', $response->setDescription($desc)->getDescription());
    }
}