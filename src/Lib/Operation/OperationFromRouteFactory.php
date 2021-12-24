<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Collection\Collection;
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
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\DocBlockUtility;

/**
 * Class OperationFromRouteFactory
 *
 * @package SwaggerBake\Lib\Operation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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

        try {
            $refClass = new ReflectionClass($route->getControllerFqn());
            $refMethod = $refClass->getMethod($route->getAction());
            $keys = (new Collection($refClass->getMethods()))->filter(function (ReflectionMethod $method) use ($route) {
                return $route->getAction() == $method->getName();
            })->toArray();
            $openApiOperation = (new AttributeFactory($refMethod, OpenApiOperation::class))->createOneOrNull();
        } catch (Exception) {
            $refClass = null;
            $refMethod = null;
            $openApiOperation = null;
        }

        if ($openApiOperation != null && !$openApiOperation->isVisible) {
            return null;
        }

        $operation = new Operation(
            operationId: $route->getName() . ':' . strtolower($httpMethod),
            httpMethod: $httpMethod,
            sortOrder: key($keys ?? []) ?? 100
        );

        $operation = $this->createOperation($operation, $route, $openApiOperation);

        $operation = (new OperationDocBlock($this->swagger, $config, $operation, $docBlock))->getOperation();

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
     * Applies Operation::tags to the Operation
     *
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param null $openApiOperation A reflection of the Controller method (i.e. action)
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    private function createOperation(
        Operation $operation,
        RouteDecorator $route,
        ?OpenApiOperation $openApiOperation
    ): Operation {
        if ($openApiOperation instanceof OpenApiOperation) {
            $operation->setSummary($openApiOperation->summary);
            $operation->setDescription($openApiOperation->description);
            $operation->setTags(count($openApiOperation->tagNames) ? $openApiOperation->tagNames : []);
            $operation->setDeprecated($openApiOperation->isDeprecated);
            if (is_array($openApiOperation->externalDocs)) {
                $operation->setExternalDocs(
                    new OperationExternalDoc(
                        $openApiOperation->externalDocs['url'] ?? '',
                        $openApiOperation->externalDocs['description'] ?? '',
                    )
                );
            }
            if (is_numeric($openApiOperation->sortOrder)) {
                $operation->setSortOrder($openApiOperation->sortOrder);
            }
        }

        if (!count($operation->getTags())) {
            $operation->setTags([
                Inflector::humanize(Inflector::underscore($route->getController())),
            ]);
        }

        return $operation;
    }
}
