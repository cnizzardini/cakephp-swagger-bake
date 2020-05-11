<?php

namespace SwaggerBake\Lib\Path;

use Exception;
use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ExpressiveRoute;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\Utility\DocBlockUtility;

class PathFromRouteFactory
{
    /** @var ExpressiveRoute */
    private $route;

    /** @var Configuration */
    private $config;

    public function __construct(ExpressiveRoute $route, Configuration $config)
    {
        $this->config = $config;
        $this->route = $route;
    }

    /**
     * Creates a Path if possible, otherwise returns null
     *
     * @return Path|null
     */
    public function create() : ?Path
    {
        $path = new Path();

        if (empty($this->route->getMethods())) {
            return null;
        }

        $docBlock = $this->getDocBlock();

        foreach ($this->route->getMethods() as $method) {

            $path
                ->setPath($this->getPathName())
                ->setSummary($docBlock ? $docBlock->getSummary() : '')
                ->setDescription($docBlock ? $docBlock->getDescription() : '')
            ;
        }

        return $path;
    }

    /**
     * @return DocBlock|null
     */
    private function getDocBlock() : ?DocBlock
    {
        if (empty($this->route->getController())) {
            return null;
        }

        $className = $this->route->getController() . 'Controller';
        $methodName = $this->route->getAction();
        $controller = $this->getControllerFromNamespaces($className);

        if (!class_exists($controller)) {
            return null;
        }

        try {
            return DocBlockUtility::getMethodDocBlock(new $controller, $methodName);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns a route (e.g. /api/model/action)
     *
     * @return string
     */
    private function getPathName() : string
    {
        $pieces = $this->getRoutablePieces();

        if ($this->config->getPrefix() == '/') {
            return implode('/', $pieces);
        }

        return substr(
            implode('/', $pieces),
            strlen($this->config->getPrefix())
        );
    }

    /**
     * Splits the route (URL) into pieces with forward-slash "/" as  the separator after removing path variables
     *
     * @return string[]
     */
    private function getRoutablePieces() : array
    {
        return array_map(
            function ($piece) {
                if (substr($piece, 0, 1) == ':') {
                    return '{' . str_replace(':', '', $piece) . '}';
                }
                return $piece;
            },
            explode('/', $this->route->getTemplate())
        );
    }

    /**
     * @param string $className
     * @return string|null
     */
    private function getControllerFromNamespaces(string $className) : ?string
    {
        $namespaces = $this->config->getNamespaces();

        if (!isset($namespaces['controllers']) || !is_array($namespaces['controllers'])) {
            throw new SwaggerBakeRunTimeException(
                'Invalid configuration, missing SwaggerBake.namespaces.controllers'
            );
        }

        foreach ($namespaces['controllers'] as $namespace) {
            $entity = $namespace . 'Controller\\' . $className;
            if (class_exists($entity, true)) {
                return $entity;
            }
        }

        return null;
    }
}