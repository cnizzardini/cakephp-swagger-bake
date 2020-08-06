<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use InvalidArgumentException;
use SwaggerBake\Lib\Decorator\RouteDecorator;

/**
 * Finds all RESTful routes that can be included in OpenAPI output based on userland configurations
 */
class RouteScanner
{
    /** @var string[]  */
    private const EXCLUDED_PLUGINS = [
        'DebugKit',
    ];

    /**
     * @var \Cake\Routing\Router
     */
    private $router;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int
     */
    private $prefixLength = 0;

    /**
     * @param \Cake\Routing\Router $router Router
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(Router $router, Configuration $config)
    {
        $this->router = $router;
        $this->prefix = $config->getPrefix();
        $this->prefixLength = strlen($this->prefix);
    }

    /**
     * Gets an array of RouteDecorator objects
     *
     * @return \SwaggerBake\Lib\Decorator\RouteDecorator[]
     */
    public function getRoutes(): array
    {
        if (empty($this->prefix) || !filter_var('http://foo.com' . $this->prefix, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('route prefix is invalid');
        }

        $filteredRoutes = array_filter($this->router::routes(), function ($route) {
            return $this->isRouteAllowed($route);
        });

        $routes = [];

        foreach ($filteredRoutes as $route) {
            $routes[$route->getName()] = new RouteDecorator($route);
        }

        ksort($routes);

        return $routes;
    }

    /**
     * @param \Cake\Routing\Route\Route $route Route
     * @return bool
     */
    private function isRouteAllowed(Route $route): bool
    {
        if (substr($route->template, 0, $this->prefixLength) != $this->prefix) {
            return false;
        }
        if (substr($route->template, $this->prefixLength) == '') {
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
