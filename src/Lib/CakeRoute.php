<?php


namespace SwaggerBake\Lib;


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
     * @return Route[]
     */
    public function getRoutes() : array
    {
        if (empty($this->prefix) || !filter_var('http://foo.com' . $this->prefix, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('route prefix is invalid');
        }

        $routes = $this->router::routes();

        return array_filter($routes, function ($route) {
            return $this->isRouteAllowed($route);
        });
    }

    /**
     * Returns controller name from the Route argument
     *
     * @param Route $route
     * @return string|null
     */
    public function getControllerFromRoute(Route $route) : ?string
    {
        $defaults = (array) $route->defaults;

        if (!isset($defaults['controller'])) {
            return null;
        }

        return $defaults['controller'];
    }

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
