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

    private int $prefixLength;

    private Configuration $config;

    /**
     * Array of RouteDecorator instances
     *
     * @var \SwaggerBake\Lib\Route\RouteDecorator[]
     */
    private $routes;

    /**
     * @param \Cake\Routing\Router $router Router
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     */
    public function __construct(Router $router, Configuration $config)
    {
        $this->router = $router;
        $this->config = $config;
        $this->prefix = $config->getPrefix();
        $this->prefixLength = strlen($this->prefix);
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
     * Reads RESTful routes from Cakes Router that matches the userland configured prefix
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

            $routeDecorator = new RouteDecorator($route);

            $controller = $routeDecorator->getController();

            $results = array_filter($classes, function ($fqn) use ($controller, $route) {
                $prefix = !empty($route->defaults['prefix']) ? $route->defaults['prefix'] . '\\' : '';

                return strstr($fqn, '\\' . $prefix . $controller . 'Controller');
            });

            if (count($results) === 1) {
                $routeDecorator->setControllerFqn('\\' . reset($results));
            }

            $routes[$route->getName()] = $routeDecorator;
        }

        ksort($routes);

        $this->routes = $routes;
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
