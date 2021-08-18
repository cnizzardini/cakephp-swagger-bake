<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Utility\Inflector;
use Exception;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeInstance;
use SwaggerBake\Lib\Attribute\OpenApiOperation;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\DocBlockUtility;

/**
 * Class OperationFromRouteFactory
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationFromRouteFactory
{
    private Swagger $swagger;

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
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
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
        $fqn = $route->getControllerFqn();
        $controllerInstance = new $fqn();

        $docBlock = $this->getDocBlock($route);
        $annotations = AnnotationUtility::getMethodAnnotations($fqn, $route->getAction());

        try {
            $refClass = new ReflectionClass($route->getControllerFqn());
            $refMethod = $refClass->getMethod($route->getAction());
        } catch (Exception) {
            $refClass = null;
            $refMethod = null;
        }

        if (!$this->isAllowed($route, $httpMethod, $refMethod) || !$this->isVisible($refMethod)) {
            return null;
        }

        $operation = (new Operation())
            ->setSummary($docBlock->getSummary())
            ->setDescription($docBlock->getDescription()->render())
            ->setHttpMethod(strtolower($httpMethod))
            ->setOperationId($route->getName() . ':' . strtolower($httpMethod));

        $operation = $this->getOperationWithTags($operation, $route, $refMethod);

        $operation = (new OperationDocBlock())
            ->getOperationWithDocBlock($operation, $docBlock);

        $operation = (new OperationPathParameter($operation, $route, $annotations, $schema))
            ->getOperationWithPathParameters();

        $operation = (new OperationHeader())
            ->getOperationWithHeaders($operation, $annotations);

        $operation = (new OperationSecurity($operation, $refMethod, $route, $controllerInstance, $this->swagger))
            ->getOperationWithSecurity();

        $operation = (new OperationQueryParameter($operation, $annotations, $controllerInstance, $schema))
            ->getOperationWithQueryParameters();

        $operation = (new OperationRequestBody($this->swagger, $operation, $annotations, $route, $schema))
            ->getOperationWithRequestBody();

        $operation = (new OperationResponse(
            $this->swagger,
            $config,
            $operation,
            $annotations,
            $route,
            $schema
        ))->getOperationWithResponses();

        $operation = (new OperationResponseException($this->swagger, $config, $operation, $docBlock))->getOperation();

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
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @return \phpDocumentor\Reflection\DocBlock
     */
    private function getDocBlock(RouteDecorator $route): DocBlock
    {
        $emptyDocBlock = DocBlockFactory::createInstance()->create('/**  */');

        if (!class_exists($route->getControllerFqn())) {
            return $emptyDocBlock;
        }

        $fqn = $route->getControllerFqn();

        try {
            return DocBlockUtility::getMethodDocBlock(new $fqn(), $route->getAction()) ?? $emptyDocBlock;
        } catch (Exception $e) {
            return $emptyDocBlock;
        }
    }

    /**
     * Is the Operation visible in Swagger UI / OpenAPI
     *
     * @param \ReflectionMethod|null $refMethod A reflection of the Controller method (i.e. action)
     * @return bool
     */
    private function isVisible(?ReflectionMethod $refMethod): bool
    {
        if ($refMethod instanceof ReflectionMethod) {
            $operation = (new AttributeInstance($refMethod, OpenApiOperation::class))->createOne();
            if ($operation instanceof OpenApiOperation) {
                return $operation->isVisible;
            }
        }

        return true;
    }

    /**
     * Is the route, http method, and annotation combination allowed? This primarily prevents HTTP PUT methods on
     * controller `edit()` actions from appearing in OpenAPI schema by default. This is because the default CakePHP
     * behavior for edit actions is HTTP PATCH.
     *
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route instance of RouteDecorator
     * @param string $httpMethod http method (PUT, POST, PATCH etc..)
     * @param \ReflectionMethod|null $refMethod A reflection of the Controller method (i.e. action)
     * @return bool
     */
    private function isAllowed(RouteDecorator $route, string $httpMethod, ?ReflectionMethod $refMethod): bool
    {
        if (strtoupper($httpMethod) !== 'PUT' || $route->getAction() !== 'edit') {
            return true;
        }

        if ($refMethod instanceof ReflectionMethod) {
            $operation = (new AttributeInstance($refMethod, OpenApiOperation::class))->createOne();
            if ($operation instanceof OpenApiOperation) {
                return $operation->isPut;
            }
        }

        return false;
    }

    /**
     * Applies Operation::tags to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \ReflectionMethod|null $refMethod A reflection of the Controller method (i.e. action)
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    private function getOperationWithTags(
        Operation $operation,
        RouteDecorator $route,
        ?ReflectionMethod $refMethod
    ): Operation {
        if ($refMethod instanceof ReflectionMethod) {
            $openApiOperation = (new AttributeInstance($refMethod, OpenApiOperation::class))->createOne();
            if ($openApiOperation instanceof OpenApiOperation && count($openApiOperation->tagNames)) {
                return $operation->setTags($openApiOperation->tagNames);
            }
        }

        return $operation->setTags([
            Inflector::humanize(Inflector::underscore($route->getController())),
        ]);
    }
}
