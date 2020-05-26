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
use SwaggerBake\Lib\Swagger;

/**
 * Class OperationFromRouteFactory
 * @package SwaggerBake\Lib\Operation
 */
class OperationFromRouteFactory
{
    /** @var Swagger  */
    private $swagger;

    public function __construct(Swagger $swagger)
    {
        $this->swagger = $swagger;
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

        $config = $this->swagger->getConfig();

        $className = $route->getController() . 'Controller';
        $fqns = NamespaceUtility::getControllerFullQualifiedNameSpace($className, $config);

        $docBlock = $this->getDocBlock($fqns, $route->getAction());
        $annotations = AnnotationUtility::getMethodAnnotations($fqns, $route->getAction());

        if (!$this->isVisible($annotations)) {
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

        $args = [$config, $operation, $docBlock, $annotations, $route, $schema];

        $operation = (new OperationDocBlock())
            ->getOperationWithDocBlock($operation, $docBlock);

        $operation = (new OperationPath())
            ->getOperationWithPathParameters($operation, $route);

        $operation = (new OperationHeader())
            ->getOperationWithHeaders($operation, $annotations);

        $operation = (new OperationSecurity($operation, $annotations, $route, new $fqns(), $this->swagger))
            ->getOperationWithSecurity();

        $operation = (new OperationQueryParameter())
            ->getOperationWithQueryParameters($operation, $annotations);

        $operation = (new OperationRequestBody(...$args))->getOperationWithRequestBody();

        $operation = (new OperationResponse(...$args))
            ->getOperationWithResponses();

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.Operation.created', $operation, [
                'config' => $config,
                'docBlock' => $docBlock,
                'methodAnnotations' => $annotations,
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