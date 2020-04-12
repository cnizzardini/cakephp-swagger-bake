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
    private $router;
    private $prefix;

    public function __construct(Router $router, Configuration $config)
    {
        $this->router = $router;
        $this->prefix = $config->getPrefix();
    }

    public function getRoutes() : array
    {
        if (empty($this->prefix) || !filter_var('http://foo.com' . $this->prefix, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('route prefix is invalid');
        }

        $length = strlen($this->prefix);

        return array_filter($this->router::routes(), function ($route) use ($length) {
            if (substr($route->template, 0, $length) != $this->prefix) {
                return null;
            }
            if (substr($route->template, $length) == '') {
                return null;
            }
            return true;
        });
    }

    public function getControllerFromRoute(Route $route) : ?string
    {
        $defaults = (array) $route->defaults;

        if (!isset($defaults['controller'])) {
            return null;
        }

        return $defaults['controller'];
    }
}
