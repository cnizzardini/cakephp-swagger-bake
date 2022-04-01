<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use ReflectionClass;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiPath;
use SwaggerBake\Lib\OpenApi\Path;
use SwaggerBake\Lib\Route\RouteDecorator;

/**
 * Creates an OpenApi Path from a route. This will apply values from the OpenApiPath attribute to the Path object.
 */
class PathFromRouteFactory
{
    /**
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     */
    public function __construct(private RouteDecorator $route)
    {
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
        if (is_null($fqn) || !class_exists($fqn)) {
            return null;
        }

        $path = new Path($this->route->templateToOpenApiPath());
        $reflection = new ReflectionClass($fqn);

        /** @var \SwaggerBake\Lib\Attribute\OpenApiPath|null $openApiPath */
        $openApiPath = (new AttributeFactory($reflection, OpenApiPath::class))->createOneOrNull();
        if ($openApiPath instanceof OpenApiPath && !$openApiPath->isVisible) {
            return null;
        }

        return $path
            ->setRef($openApiPath->ref ?? $path->getRef())
            ->setDescription($openApiPath->description ?? $path->getDescription())
            ->setSummary($openApiPath->summary ?? $path->getSummary())
            ->setTags($openApiPath->tags ?? []);
    }
}
