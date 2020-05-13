<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Http\Exception\InternalErrorException;
use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationDocBlock;

class OperationDocBlockTest extends TestCase
{
    /**
     * @throws InternalErrorException
     */
    public function testGetOperationWithDocBlock()
    {
        $operation = (new OperationDocBlock())
            ->getOperationWithDocBlock(
                new Operation(),
                DocBlockFactory::createInstance()->create('/** @see http://www.cakephp.org CakePHP */')
            );

        $doc = $operation->getExternalDocs();

        $this->assertEquals('CakePHP', $doc->getDescription());
        $this->assertEquals('http://www.cakephp.org', $doc->getUrl());
    }
}