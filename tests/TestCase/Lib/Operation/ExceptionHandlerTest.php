<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Operation\ExceptionHandler;

class ExceptionHandlerTest extends TestCase
{
    public function testErrorCodes()
    {
        $exceptions = [
            400 => 'BadRequestException',
            401 => 'UnauthorizedException',
            403 => 'ForbiddenException',
            404 => 'RecordNotFoundException',
            405 => 'MethodNotAllowedException',
            500 => 'Exception',
        ];

        $factory = DocBlockFactory::createInstance();
        foreach ($exceptions as $code => $exception) {
            $throws = $factory->create("/** @throws $exception */ */")->getTagsByName('throws')[0];
            $this->assertEquals($code, (new ExceptionHandler($throws))->getCode());
        }
    }

    public function testMessage()
    {
        $factory = DocBlockFactory::createInstance();
        $throws = $factory->create("/** @throws Exception description */")->getTagsByName('throws')[0];
        $this->assertEquals('description', (new ExceptionHandler($throws))->getMessage());
    }
}