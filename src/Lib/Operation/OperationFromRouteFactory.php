<?php

namespace SwaggerBake\Lib\Operation;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagOperation;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\DocBlockUtility;
use SwaggerBake\Lib\Utility\NamespaceUtility;

/**
 * Class OperationFromRouteFactory
 * @package SwaggerBake\Lib\Operation
 */
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
     * @param RouteDecorator $route
     * @param string $httpMethod
     * @param null|Schema $schema
     * @return Operation|null
     */
    public function create(RouteDecorator $route, string $httpMethod, ?Schema $schema) : ?Operation
    {
        if (empty($route->getMethods())) {
            return null;
        }

        $className = $route->getController() . 'Controller';
        $fullyQualifiedNameSpace = NamespaceUtility::getControllerFullQualifiedNameSpace($className, $this->config);

        $docBlock = $this->getDocBlock($fullyQualifiedNameSpace, $route->getAction());
        $methodAnnotations = AnnotationUtility::getMethodAnnotations($fullyQualifiedNameSpace, $route->getAction());

        if (!$this->isVisible($methodAnnotations)) {
            return null;
        }

        $operation = (new Operation())
            ->setSummary($docBlock->getSummary())
            ->setDescription($docBlock->getDescription())
            ->setHttpMethod(strtolower($httpMethod))
            ->setOperationId($route->getName())
            ->setTags([
                Inflector::humanize(Inflector::underscore($route->getController()))
            ]);

        $operation = (new OperationDocBlock())
            ->getOperationWithDocBlock($operation, $docBlock);

        $operation = (new OperationPath())
            ->getOperationWithPathParameters($operation, $route);

        $operation = (new OperationHeader())
            ->getOperationWithHeaders($operation, $methodAnnotations);

        $operation = (new OperationSecurity())
            ->getOperationWithSecurity($operation, $methodAnnotations);

        $operation = (new OperationQueryParameter())
            ->getOperationWithQueryParameters($operation, $methodAnnotations);

        $operation = (new OperationRequestBody($this->config, $operation, $docBlock, $methodAnnotations, $route, $schema))
            ->getOperationWithRequestBody();

        $operation = (new OperationResponse($this->config, $operation, $docBlock, $methodAnnotations, $route, $schema))
            ->getOperationWithResponses();

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.Operation.created', $operation, [
                'config' => $this->config,
                'docBlock' => $docBlock,
                'methodAnnotations' => $methodAnnotations,
                'route' => $route,
                'schema' => $schema,
            ])
        );

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
        $emptyDocBlock = DocBlockFactory::createInstance()->create('/**  */');

        if (!class_exists($fullyQualifiedNameSpace)) {
            return $emptyDocBlock;
        }

        try {
            return DocBlockUtility::getMethodDocBlock(new $fullyQualifiedNameSpace, $methodName) ?? $emptyDocBlock;
        } catch (Exception $e) {
            return $emptyDocBlock;
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