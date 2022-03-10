<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Route;

use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use InvalidArgumentException;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Utility\NamespaceUtility;

/**
 * Finds all RESTful routes that can be included in OpenAPI output based on userland configurations
 */
class RouteScanner
{
    /** @var string[]  */
    private const EXCLUDED_PLUGINS = [
        'DebugKit',
    ];

    private Router $router;

    private string $prefix;

    private Configuration $config;

    /**
     * Array of RouteDecorator instances
     *
     * @var \SwaggerBake\Lib\Route\RouteDecorator[]
     */
    private array $routes;

    /**
     * @param \Cake\Routing\Router $router Router
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(Router $router, Configuration $config)
    {
        $this->router = $router;
        $this->config = $config;
        $this->prefix = $config->getPrefix();
        $this->loadRoutes();
    }

    /**
     * @return \SwaggerBake\Lib\Route\RouteDecorator[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Reads RESTful routes from Cakes Router that matches the user land configured prefix
     *
     * @return void
     * @throws \Exception
     */
    private function loadRoutes(): void
    {
        $namespaces = $this->config->getNamespaces();
        $classes = NamespaceUtility::getClasses($namespaces['controllers'], 'Controller');

        if (empty($this->prefix) || !filter_var('http://foo.com' . $this->prefix, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('route prefix is invalid');
        }

        $routes = [];

        foreach ($this->router::routes() as $route) {
            if (!$this->isRouteAllowed($route)) {
                continue;
            }

            $routes[$route->getName()] = $this->createRouteDecorator($route, $classes);
        }

        ksort($routes);

        $this->routes = $routes;
    }

    /**
     * Creates a RouteDecorator from a route. The $classes argument should provide a list of all controller classes
     * so the route can be matched to a Controller class.
     *
     * @param \Cake\Routing\Route\Route $route The Route to be decorated
     * @param array $classes An array of controller classes
     * @return \SwaggerBake\Lib\Route\RouteDecorator
     */
    private function createRouteDecorator(Route $route, array $classes): RouteDecorator
    {
        $routeDecorator = new RouteDecorator($route);
        $path = 'Controller\\';
        $path .= $routeDecorator->getPrefix() ? $routeDecorator->getPrefix() . '\\' : '';
        $path .= $routeDecorator->getController() . 'Controller';

        $results = array_filter($classes, function ($fqn) use ($path) {
            return str_contains($fqn, $path);
        });

        if (count($results) === 1) {
            $fqn = reset($results);
            if (is_string($fqn) && class_exists($fqn)) {
                $routeDecorator->setControllerFqn("\\$fqn");
            }
        }

        return $routeDecorator;
    }

    /**
     * @param \Cake\Routing\Route\Route $route Route
     * @return bool
     */
    private function isRouteAllowed(Route $route): bool
    {
        if (!str_starts_with($route->template, $this->prefix) || empty($route->template)) {
            return false;
        }

        $defaults = (array)$route->defaults;

        if (!isset($defaults['_method']) || empty($defaults['_method'])) {
            return false;
        }

        if (isset($defaults['plugin']) && in_array($defaults['plugin'], self::EXCLUDED_PLUGINS)) {
            return false;
        }

        return true;
    }
}
