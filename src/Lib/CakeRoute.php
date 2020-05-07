<?php

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Model\ExpressiveRoute;
use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use InvalidArgumentException;

/**
 * Class CakeRoute
 * Gets an array of routes matching a given route prefix
 */
class CakeRoute
{
    /** @var string[]  */
    private const EXCLUDED_PLUGINS = [
        'DebugKit'
    ];

    /** @var Router  */
    private $router;

    /** @var string  */
    private $prefix;

    /** @var int  */
    private $prefixLength = 0;

    public function __construct(Router $router, Configuration $config)
    {
        $this->router = $router;
        $this->prefix = $config->getPrefix();
        $this->prefixLength = strlen($this->prefix);
    }

    /**
     * Gets an array of Route
     *
     * @return ExpressiveRoute[]
     */
    public function getRoutes() : array
    {
        if (empty($this->prefix) || !filter_var('http://foo.com' . $this->prefix, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('route prefix is invalid');
        }

        $filteredRoutes = array_filter($this->router::routes(), function ($route) {
            return $this->isRouteAllowed($route);
        });

        $routes = [];

        foreach ($filteredRoutes as $route) {
            $routes[$route->getName()] = $this->createExpressiveRouteFromRoute($route);
        }

        ksort($routes);

        return $routes;
    }

    /**
     * @param Route $route
     * @return ExpressiveRoute
     */
    private function createExpressiveRouteFromRoute(Route $route) : ExpressiveRoute
    {
        $defaults = (array) $route->defaults;

        $methods = $defaults['_method'];
        if (!is_array($defaults['_method'])) {
            $methods = explode(', ', $defaults['_method']);
        }

        return (new ExpressiveRoute())
            ->setPlugin($defaults['plugin'])
            ->setController($defaults['controller'])
            ->setName($route->getName())
            ->setAction($defaults['action'])
            ->setMethods($methods)
            ->setTemplate($route->template)
        ;
    }

    /**
     * @param Route $route
     * @return bool
     */
    private function isRouteAllowed(Route $route) : bool
    {
        if (substr($route->template, 0, $this->prefixLength) != $this->prefix) {
            return false;
        }
        if (substr($route->template, $this->prefixLength) == '') {
            return false;
        }

        $defaults = (array) $route->defaults;

        if (!isset($defaults['_method']) || empty($defaults['_method'])) {
            return false;
        }

        if (isset($defaults['plugin']) && in_array($defaults['plugin'], self::EXCLUDED_PLUGINS)) {
            return false;
        }

        return true;
    }
}
