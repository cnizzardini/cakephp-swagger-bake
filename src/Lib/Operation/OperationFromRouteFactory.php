<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use SwaggerBake\Lib\Annotation\SwagOperation;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\DocBlockUtility;
use SwaggerBake\Lib\Utility\NamespaceUtility;

/**
 * Class OperationFromRouteFactory
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationFromRouteFactory
{
    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    private $swagger;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     */
    public function __construct(Swagger $swagger)
    {
        $this->swagger = $swagger;
    }

    /**
     * Creates an instance of Operation
     *
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator $route RouteDecorator
     * @param string $httpMethod Http method such i.e. PUT, POST, PATCH, GET, and DELETE
     * @param null|\SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return \SwaggerBake\Lib\OpenApi\Operation|null
     */
    public function create(RouteDecorator $route, string $httpMethod, ?Schema $schema): ?Operation
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
            ->setDescription($docBlock->getDescription()->render())
            ->setHttpMethod(strtolower($httpMethod))
            ->setOperationId($route->getName());

        $operation = $this->getOperationWithTags($operation, $route, $annotations);

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

        $operation = (new OperationRequestBody($config, $operation, $annotations, $route, $schema))
            ->getOperationWithRequestBody();

        $operation = (new OperationResponse($config, $operation, $docBlock, $annotations, $route, $schema))
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
     * @param string $fullyQualifiedNameSpace Fully qualified namespace of the controller
     * @param string $methodName Controller action (method) name
     * @return \phpDocumentor\Reflection\DocBlock
     */
    private function getDocBlock(string $fullyQualifiedNameSpace, string $methodName): DocBlock
    {
        $emptyDocBlock = DocBlockFactory::createInstance()->create('/**  */');

        if (!class_exists($fullyQualifiedNameSpace)) {
            return $emptyDocBlock;
        }

        try {
            return DocBlockUtility::getMethodDocBlock(new $fullyQualifiedNameSpace(), $methodName) ?? $emptyDocBlock;
        } catch (Exception $e) {
            return $emptyDocBlock;
        }
    }

    /**
     * Is the Operation visible in Swagger UI / OpenAPI
     *
     * @param array $annotations An array of annotation objects
     * @return bool
     */
    private function isVisible(array $annotations): bool
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

    /**
     * Applies Operation::tags to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator $route RouteDecorator
     * @param array $annotations An array of annotation objects
     * @return \SwaggerBake\Lib\OpenApi\Operation ]
     */
    private function getOperationWithTags(Operation $operation, RouteDecorator $route, array $annotations): Operation
    {
        $swagOperations = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagOperation;
        });

        $swagOperation = reset($swagOperations);

        if (empty($swagOperation->tagNames)) {
            return $operation->setTags([
                Inflector::humanize(Inflector::underscore($route->getController())),
            ]);
        }

        return $operation->setTags($swagOperation->tagNames);
    }
}
