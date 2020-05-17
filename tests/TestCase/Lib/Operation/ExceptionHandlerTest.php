<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Operation\ExceptionHandler;

class ExceptionHandlerTest extends TestCase
{
    /**
     * @throws InternalErrorException
     */
    public function testConstruct()
    {
        $this->assertEquals(400, (new ExceptionHandler('BadRequestException'))->getCode());
        $this->assertEquals(401, (new ExceptionHandler('UnauthorizedException'))->getCode());
        $this->assertEquals(403, (new ExceptionHandler('ForbiddenException'))->getCode());
        $this->assertEquals(404, (new ExceptionHandler('RecordNotFoundException'))->getCode());
        $this->assertEquals(405, (new ExceptionHandler('MethodNotAllowedException'))->getCode());
        $this->assertEquals(500, (new ExceptionHandler('Exception'))->getCode());
    }
}