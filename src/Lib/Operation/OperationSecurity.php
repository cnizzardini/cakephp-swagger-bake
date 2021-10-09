<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Controller\Controller;
use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiSecurity;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\PathSecurity;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;

/**
 * Class OperationSecurity
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationSecurity
{
    private Operation $operation;

    private ?ReflectionMethod $refMethod;

    private RouteDecorator $route;

    private Controller $controller;

    private Swagger $swagger;

    /**
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \ReflectionMethod|null $refMethod ReflectionMethod or null
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \Cake\Controller\Controller $controller Controller
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     */
    public function __construct(
        Operation $operation,
        ?ReflectionMethod $refMethod,
        RouteDecorator $route,
        Controller $controller,
        Swagger $swagger
    ) {
        $this->operation = $operation;
        $this->refMethod = $refMethod;
        $this->route = $route;
        $this->controller = $controller;
        $this->swagger = $swagger;
    }

    /**
     * Gets an Operation instance after applying security
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithSecurity(): Operation
    {
        $this->assignOpenApiSecurity();
        $this->assignAuthenticationComponent();

        return $this->operation;
    }

    /**
     * Assigns OpenApiSecurity attribute
     *
     * @return void
     */
    private function assignOpenApiSecurity(): void
    {
        if (!$this->refMethod instanceof ReflectionMethod) {
            return;
        }

        $securities = (new AttributeFactory($this->refMethod, OpenApiSecurity::class))->createMany();
        if (empty($securities)) {
            return;
        }

        foreach ($securities as $sec) {
            $this->operation->pushSecurity(
                (new PathSecurity())
                    ->setName($sec->name)
                    ->setScopes($sec->scopes)
            );
        }
    }

    /**
     * Assign by AuthenticationComponent
     *
     * @return void
     */
    private function assignAuthenticationComponent(): void
    {
        if (!isset($this->controller->Authentication)) {
            return;
        }

        if (in_array($this->route->getAction(), $this->controller->Authentication->getUnauthenticatedActions())) {
            return;
        }

        $array = $this->swagger->getArray();
        if (!isset($array['components']['securitySchemes']) || count($array['components']['securitySchemes']) !== 1) {
            return;
        }

        $scheme = array_keys($array['components']['securitySchemes'])[0];

        $securities = $this->operation->getSecurity();
        if (array_key_exists($scheme, $securities)) {
            return;
        }

        $this->operation->pushSecurity(
            (new PathSecurity())
                ->setName($scheme)
                ->setScopes([])
        );
    }
}
