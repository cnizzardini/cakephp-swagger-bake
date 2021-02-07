<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Operation\ExceptionHandler;

class ExceptionHandlerTest extends TestCase
{
    public function testErrorCodes(): void
    {
        $exceptions = [
            '400' => '\Cake\Http\Exception\BadRequestException',
            '401' => '\Cake\Http\Exception\UnauthorizedException',
            '403' => '\Cake\Http\Exception\ForbiddenException',
            '404' => '\Cake\Datasource\Exception\RecordNotFoundException',
            '405' => '\Cake\Http\Exception\MethodNotAllowedException',
            '500' => '\Exception'
        ];

        $factory = DocBlockFactory::createInstance();
        foreach ($exceptions as $code => $exception) {
            /** @var \phpDocumentor\Reflection\DocBlock\Tags\Throws $throws */
            $throws = $factory->create("/** @throws $exception */ */")->getTagsByName('throws')[0];
            $this->assertEquals($code, (new ExceptionHandler($throws))->getCode());
        }
    }

    public function testMessage(): void
    {
        $factory = DocBlockFactory::createInstance();
        /** @var \phpDocumentor\Reflection\DocBlock\Tags\Throws $throws */
        $throws = $factory->create("/** @throws Exception description */")->getTagsByName('throws')[0];
        $this->assertEquals('description', (new ExceptionHandler($throws))->getMessage());
    }
}