<?php

namespace SwaggerBake\Lib\Operation;

use Cake\Utility\Inflector;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\DocBlockUtility;
use SwaggerBake\Lib\Utility\NamespaceUtility;

class OperationFromRouteFactory
{
    /** @var Configuration  */
    private $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Creates an instance of Operation
     *
     * @param ExpressiveRoute $route
     * @param string $httpMethod
     * @return Operation|null
     */
    public function create(ExpressiveRoute $route, string $httpMethod) : ?Operation
    {
        if (empty($route->getMethods())) {
            return null;
        }

        $operation = (new Operation())
            ->setHttpMethod(strtolower($httpMethod))
            ->setOperationId($route->getName())
            ->setTags([
                Inflector::humanize(Inflector::underscore($route->getController()))
            ]);

        $className = $route->getController() . 'Controller';
        $fullyQualifiedNameSpace = NamespaceUtility::getController($className, $this->config);

        $methodAnnotations = AnnotationUtility::getMethodAnnotations($fullyQualifiedNameSpace, $route->getAction());
        $doc = $this->getDocBlock($fullyQualifiedNameSpace, $route->getAction());

        $operation = (new OperationPath())
            ->getOperationWithPathParameters($operation, $route);

        $operation = (new OperationHeader())
            ->getOperationWithHeaders($operation, $methodAnnotations);

        $operation = (new OperationSecurity())
            ->getOperationWithSecurity($operation, $methodAnnotations);

        $operation = (new OperationQueryParameter())
            ->getOperationWithQueryParameters($operation, $methodAnnotations);

        $operation = (new OperationForm())
            ->getOperationWithFormProperties($operation, $methodAnnotations);

        $operation = (new OperationResponse())
            ->getOperationWithResponses($operation, $doc, $methodAnnotations);

        return $operation;
    }

    /**
     * @param string $fullyQualifiedNameSpace
     * @param string $methodName
     * @return DocBlock
     */
    private function getDocBlock(string $fullyQualifiedNameSpace, string $methodName) : DocBlock
    {
        if (!class_exists($fullyQualifiedNameSpace)) {
            return DocBlockFactory::createInstance()->create('');
        }

        try {
            return DocBlockUtility::getMethodDocBlock(new $fullyQualifiedNameSpace, $methodName);
        } catch (Exception $e) {
            return DocBlockFactory::createInstance()->create('');
        }
    }
}