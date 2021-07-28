<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Path;

use SwaggerBake\Lib\Annotation\SwagPath;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Utility\AnnotationUtility;

/**
 * Class PathFromRouteFactory
 *
 * @package SwaggerBake\Lib\Path
 */
class PathFromRouteFactory
{
    private RouteDecorator $route;

    /**
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     */
    public function __construct(RouteDecorator $route)
    {
        $this->route = $route;
    }

    /**
     * Creates an instance of Path if possible, otherwise returns null
     *
     * @return \SwaggerBake\Lib\OpenApi\Path|null
     */
    public function create(): ?Path
    {
        if (empty($this->route->getMethods())) {
            return null;
        }

        $fqn = $this->route->getControllerFqn();

        if (is_null($fqn)) {
            return null;
        }

        $path = (new Path())->setResource($this->route->templateToOpenApiPath());

        $swagPath = $this->getSwagPathAnnotation($fqn);

        if (is_null($swagPath)) {
            return $path;
        }

        if ($swagPath->isVisible === false) {
            return null;
        }

        return $path
            ->setRef($swagPath->ref ?? null)
            ->setDescription($swagPath->description ?? null)
            ->setSummary($swagPath->summary ?? null);
    }

    /**
     * Returns SwagPath if the controller has the annotation, otherwise null
     *
     * @param string $fqns Full qualified namespace of the Controller
     * @return \SwaggerBake\Lib\Annotation\SwagPath|null
     */
    private function getSwagPathAnnotation(string $fqns): ?SwagPath
    {
        $annotations = AnnotationUtility::getClassAnnotationsFromFqns($fqns);

        $results = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagPath;
        });

        if (empty($results)) {
            return null;
        }

        return reset($results);
    }
}
