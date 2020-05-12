<?php

namespace SwaggerBake\Test\TestCase\Lib\Operation;

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use SwaggerBake\Lib\Annotation\SwagHeader;
use SwaggerBake\Lib\AnnotationLoader;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Operation\OperationFromRouteFactory;
use SwaggerBake\Lib\Operation\OperationHeader;
use SwaggerBake\Lib\Swagger;

class OperationHeaderTest extends TestCase
{
    public function testGetOperationWithHeaders()
    {
        $operation = (new OperationHeader())
            ->getOperationWithHeaders(
                new Operation(),
                [new SwagHeader(['name' => 'X-HEADER','type' => 'string', 'description' => '', 'required' => false])]
            );

        $parameters = $operation->getParameters();
        $param = reset($parameters);
        $this->assertEquals('X-HEADER', $param->getName());
        $this->assertEquals('header', $param->getIn());
    }
}