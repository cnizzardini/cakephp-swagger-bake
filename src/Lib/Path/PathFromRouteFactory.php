<?php

namespace SwaggerBake\Lib\Path;

use SwaggerBake\Lib\Annotation\SwagPath;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\Utility\AnnotationUtility;
use SwaggerBake\Lib\Utility\NamespaceUtility;

/**
 * Class PathFromRouteFactory
 * @package SwaggerBake\Lib\Path
 */
class PathFromRouteFactory
{
    /** @var RouteDecorator */
    private $route;

    /** @var Configuration */
    private $config;

    public function __construct(RouteDecorator $route, Configuration $config)
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
        if (empty($this->route->getMethods())) {
            return null;
        }

        $controller = $this->route->getController() . 'Controller';
        $fullyQualifiedNamespace = NamespaceUtility::getControllerFullQualifiedNameSpace($controller, $this->config);

        if (is_null($fullyQualifiedNamespace) || !$this->isVisible($fullyQualifiedNamespace)) {
            return null;
        }

        return (new Path())->setResource($this->getResourceName());
    }

    /**
     * @param string $fullyQualifiedNamespace
     * @return bool
     */
    private function isVisible(string $fullyQualifiedNamespace) : bool
    {
        $annotations = AnnotationUtility::getClassAnnotationsFromFqns($fullyQualifiedNamespace);

        $results = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagPath;
        });

        if (empty($results)) {
            return true;
        }

        $swagPath = reset($results);

        return $swagPath->isVisible;
    }

    /**
     * Returns a routes resource (e.g. /api/model/action)
     *
     * @return string
     */
    private function getResourceName() : string
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
}