<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Http\Exception\InternalErrorException;
use Cake\TestSuite\TestCase;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\Operation\OperationResponse;

class OperationResponseTest extends TestCase
{
    /**
     * @throws InternalErrorException
     */
    public function testGetOperationWithResponses()
    {
        $docFactory = DocBlockFactory::createInstance();
        $doc = $docFactory->create('/** @throws Exception */');

        $operation = (new OperationResponse())
            ->getOperationWithResponses(
                new Operation(),
                $doc,
                [
                    new SwagResponseSchema([
                        'refEntity' => '',
                        'httpCode' => 200,
                        'description' => '',
                        'mimeType' => '',
                        'schemaType' => '',
                        'schemaFormat' => ''
                    ]),
                ]
            );

        $this->assertInstanceOf(Response::class, $operation->getResponseByCode(200));
        $this->assertInstanceOf(Response::class, $operation->getResponseByCode(500));
    }
}