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
use SwaggerBake\Lib\Attribute\AttributeFactory;
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
    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     */
    public function __construct(private Swagger $swagger)
    {
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

        if (!$this->isAllowed($route, $httpMethod, $refMethod)) {
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

        $operation = (new OperationPathParameter($operation, $route, $refMethod, $schema))
            ->getOperationWithPathParameters();

        $operation = (new OperationHeader())
            ->getOperationWithHeaders($operation, $refMethod);

        $operation = (new OperationSecurity($operation, $refMethod, $route, $controllerInstance, $this->swagger))
            ->getOperationWithSecurity();

        $operation = (new OperationQueryParameter($operation, $controllerInstance, $schema, $refMethod))
            ->getOperationWithQueryParameters();

        $operation = (new OperationRequestBody($this->swagger, $operation, $route, $refMethod, $schema))
            ->getOperationWithRequestBody();

        $operation = (new OperationResponse(
            $this->swagger,
            $config,
            $operation,
            $route,
            $schema,
            $refMethod,
        ))->getOperationWithResponses();

        $operation = (new OperationResponseException($this->swagger, $config, $operation, $docBlock))->getOperation();

        EventManager::instance()->dispatch(
            new Event('SwaggerBake.Operation.created', $operation, [
                'config' => $config,
                'docBlock' => $docBlock,
                'reflectionMethod' => $refMethod,
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
     * First check if the route (operation) is visible. Then check ifs the route, http method, and annotation
     * combination allowed? This primarily prevents HTTP PUT methods on controller `edit()` actions from appearing in
     * OpenAPI schema by default. This is because the default CakePHP behavior for edit actions is HTTP PATCH.
     *
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route instance of RouteDecorator
     * @param string $httpMethod http method (PUT, POST, PATCH etc..)
     * @param \ReflectionMethod|null $refMethod A reflection of the Controller method (i.e. action)
     * @return bool
     */
    private function isAllowed(RouteDecorator $route, string $httpMethod, ?ReflectionMethod $refMethod): bool
    {
        $operation = null;

        if ($refMethod instanceof ReflectionMethod) {
            $operation = (new AttributeFactory($refMethod, OpenApiOperation::class))->createOneOrNull();
            if ($operation instanceof OpenApiOperation && !$operation->isVisible) {
                return false;
            }
        }

        if (strtoupper($httpMethod) !== 'PUT' || $route->getAction() !== 'edit') {
            return true;
        }

        return $operation instanceof OpenApiOperation ? $operation->isPut : false;
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
            $openApiOperation = (new AttributeFactory($refMethod, OpenApiOperation::class))->createOneOrNull();
            if ($openApiOperation instanceof OpenApiOperation && count($openApiOperation->tagNames)) {
                return $operation->setTags($openApiOperation->tagNames);
            }
        }

        return $operation->setTags([
            Inflector::humanize(Inflector::underscore($route->getController())),
        ]);
    }
}
