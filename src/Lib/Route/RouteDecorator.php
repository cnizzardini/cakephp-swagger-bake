<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Route;

use Cake\Routing\Route\Route;
use MixerApi\Core\Model\Model;

/**
 * Class RouteDecorator
 *
 * @package SwaggerBake\Lib\Decorator
 *
 * Decorates a Cake\Routing\Route\Route
 */
class RouteDecorator
{
    private Route $route;

    private ?string $name;

    private ?string $plugin;

    private ?string $prefix;

    private ?string $controller;

    private ?string $action;

    private array $methods = [];

    private ?string $template;

    private ?string $controllerFqn = null;

    private ?Model $model = null;

    /**
     * @param \Cake\Routing\Route\Route $route Route
     */
    public function __construct(Route $route)
    {
        $defaults = (array)$route->defaults;

        $methods = $defaults['_method'];
        if (isset($defaults['_method']) && !is_array($defaults['_method'])) {
            $methods = explode(', ', $defaults['_method']);
        }

        $this
            ->setRoute($route)
            ->setTemplate($route->template)
            ->setName($route->getName())
            ->setPlugin($defaults['plugin'])
            ->setPrefix($defaults['prefix'] ?? null)
            ->setController($defaults['controller'] ?? null)
            ->setAction($defaults['action'])
            ->setMethods($methods ?? []);
    }

    /**
     * @return \Cake\Routing\Route\Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @param \Cake\Routing\Route\Route $route Route
     * @return $this
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name Name of the route
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix The routing prefix, for example "Admin" when controller is in App\Controller\Admin
     * @return $this
     */
    public function setPrefix(?string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    /**
     * @param string|null $plugin Name of the plugin this route is associated with
     * @return $this
     */
    public function setPlugin(?string $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * @return string
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string|null $controller Name of the Controller this route is associated with
     * @return $this
     */
    public function setController(?string $controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action The controller method
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param array $methods HTTP methods
     * @return $this
     */
    public function setMethods(array $methods)
    {
        $this->methods = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @param string $template The templated route
     * @return $this
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getControllerFqn(): ?string
    {
        return $this->controllerFqn;
    }

    /**
     * @param string $controllerFqn controller fqn
     * @return $this
     */
    public function setControllerFqn(string $controllerFqn)
    {
        $this->controllerFqn = $controllerFqn;

        return $this;
    }

    /**
     * @return \MixerApi\Core\Model\Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @param \MixerApi\Core\Model\Model|null $model The model associated with this route
     * @return $this
     */
    public function setModel(?Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Converts a CakePHP route template to an OpenAPI path
     *
     * @return string
     */
    public function templateToOpenApiPath(): string
    {
        $pieces = array_map(
            function ($piece) {
                if (str_starts_with($piece, ':')) {
                    return '{' . str_replace(':', '', $piece) . '}';
                }

                return $piece;
            },
            explode('/', $this->template)
        );

        return implode('/', $pieces);
    }
}
