<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Route;

use Cake\Routing\Route\Route;
use Cake\Routing\Router;
use InvalidArgumentException;
use SwaggerBake\Lib\Configuration;

/**
 * Finds all RESTful routes that can be included in OpenAPI output based on userland configurations
 */
class RouteScanner
{
    /** @var string[]  */
    private const EXCLUDED_PLUGINS = [
        'DebugKit',
    ];

    private string $prefix;

    /**
     * Array of RouteDecorator instances
     *
     * @var \SwaggerBake\Lib\Route\RouteDecorator[]
     */
    private array $routes;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private Configuration $config;

    /**
     * @param \Cake\Routing\Router $router CakePHP Router
     * @param \SwaggerBake\Lib\Configuration $config Swagger Configuration
     */
    public function __construct(private Router $router, Configuration $config)
    {
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
     * Reads RESTful routes from the CakePHP Router. Routes must the prefix config option in the `swagger_bake` config.
     *
     * @return void
     */
    private function loadRoutes(): void
    {
        if (empty($this->prefix) || !filter_var('http://example.com' . $this->prefix, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('route prefix is invalid');
        }

        $routes = [];

        if (!$this->config->isHotReload() && PHP_SAPI !== 'cli') {
            $this->routes = $routes;
            
            return;
        }

        foreach ($this->router::routes() as $route) {
            if (!$this->isRouteAllowed($route)) {
                continue;
            }

            $routes[$route->getName()] = new RouteDecorator($route);
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
        if (!str_starts_with($route->template, $this->prefix) || empty($route->template)) {
            return false;
        }

        $defaults = $route->defaults;
        if (empty($defaults['_method'])) {
            return false;
        }
        if (isset($defaults['plugin']) && in_array($defaults['plugin'], self::EXCLUDED_PLUGINS)) {
            return false;
        }

        return true;
    }
}
