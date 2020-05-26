<?php

namespace SwaggerBake\Lib\Operation;

use Cake\Controller\Controller;
use SwaggerBake\Lib\Annotation\SwagSecurity;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\PathSecurity;
use SwaggerBake\Lib\Swagger;

/**
 * Class OperationSecurity
 * @package SwaggerBake\Lib\Operation
 */
class OperationSecurity
{
    /** @var Operation  */
    private $operation;

    /** @var array  */
    private $annotations;

    /** @var RouteDecorator  */
    private $route;

    /** @var Controller  */
    private $controller;

    /** @var Swagger  */
    private $swagger;

    public function __construct(
        Operation $operation,
        array $annotations,
        RouteDecorator $route,
        Controller $controller,
        Swagger $swagger
    ) {
        $this->operation = $operation;
        $this->annotations = $annotations;
        $this->route = $route;
        $this->controller = $controller;
        $this->swagger = $swagger;
    }


    /**
     * Gets an Operation instance after applying security
     *
     * @return Operation
     */
    public function getOperationWithSecurity() : Operation
    {
        $this->assignSwagSecurityAnnotations();
        $this->assignAuthenticationComponent();

        return $this->operation;
    }

    /**
     * Assigns @SwagSecurity annotations
     *
     * @return void
     */
    private function assignSwagSecurityAnnotations() : void
    {
        $swagSecurities = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagSecurity;
        });

        foreach ($swagSecurities as $annotation) {
            $this->operation->pushSecurity(
                (new PathSecurity())
                    ->setName($annotation->name)
                    ->setScopes($annotation->scopes)
            );
        }
    }

    /**
     * Assign by AuthenticationComponent
     *
     * @return void
     */
    private function assignAuthenticationComponent() : void
    {
        if (!isset($this->controller->Authentication)) {
            return;
        }

        if (in_array($this->route->getAction(), $this->controller->Authentication->getUnauthenticatedActions())) {
            return;
        }

        $array = $this->swagger->getArray();
        if (count($array['components']['securitySchemes']) !== 1) {
            return;
        }

        $scheme = array_keys($array['components']['securitySchemes'])[0];

        $this->operation->pushSecurity(
            (new PathSecurity())
                ->setName($scheme)
                ->setScopes([])
        );
    }
}