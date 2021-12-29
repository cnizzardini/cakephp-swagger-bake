<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Schema;

class ContentTest extends TestCase
{
    public function test(): void
    {
        $content = new Content('', '');
        $this->assertEquals($mimeType = 'application/json', $content->setMimeType($mimeType)->getMimeType());
        $this->assertEquals($schema = new Schema(), $content->setSchema($schema)->getSchema());
        $array = $content->toArray();
        $this->assertArrayNotHasKey('required', $array);
    }
}