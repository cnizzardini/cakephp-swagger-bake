<?php

namespace SwaggerBake\Test\TestCase\Lib\OpenApi;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;

class OperationExternalDocTest extends TestCase
{
    public function test_json_serialize(): void
    {
        $this->assertIsArray((new OperationExternalDoc('http://localhost', 'test'))->jsonSerialize());
    }
}