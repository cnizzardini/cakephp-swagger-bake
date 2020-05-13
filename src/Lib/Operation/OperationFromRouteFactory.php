<?php

namespace SwaggerBake\Lib\Operation;

use Cake\Utility\Inflector;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagOperation;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;
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
     * @param null|Schema $schema
     * @return Operation|null
     */
    public function create(ExpressiveRoute $route, string $httpMethod, ?Schema $schema) : ?Operation
    {
        if (empty($route->getMethods())) {
            return null;
        }

        $className = $route->getController() . 'Controller';
        $fullyQualifiedNameSpace = NamespaceUtility::getController($className, $this->config);

        $doc = $this->getDocBlock($fullyQualifiedNameSpace, $route->getAction());
        $methodAnnotations = AnnotationUtility::getMethodAnnotations($fullyQualifiedNameSpace, $route->getAction());

        if (!$this->isVisible($methodAnnotations)) {
            return null;
        }

        $operation = (new Operation())
            ->setSummary($doc->getSummary())
            ->setDescription($doc->getDescription())
            ->setHttpMethod(strtolower($httpMethod))
            ->setOperationId($route->getName())
            ->setTags([
                Inflector::humanize(Inflector::underscore($route->getController()))
            ]);

        $operation = (new OperationDocBlock())
            ->getOperationWithDocBlock($operation, $doc);

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

        $operation = (new OperationResponse($this->config, $operation, $doc, $methodAnnotations, $route, $schema))
            ->getOperationWithResponses();

        return $operation;
    }

    /**
     * Gets an instance of DocBlock from the controllers method
     *
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

    /**
     * @param array $annotations
     * @return bool
     */
    private function isVisible(array $annotations) : bool
    {
        $swagOperations = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagOperation;
        });

        if (empty($swagOperations)) {
            return true;
        }

        $swagOperation = reset($swagOperations);

        return $swagOperation->isVisible === false ? false : true;
    }
}