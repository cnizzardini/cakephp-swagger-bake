<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Route;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Routing\Route\Route;
use Cake\Utility\Inflector;
use MixerApi\Core\Model\Model;
use ReflectionClass;

/**
 * Decorates \Cake\Routing\Route\Route
 */
class RouteDecorator
{
    /**
     * Route::name
     *
     * @var string|null
     */
    private ?string $name;

    /**
     * Route::defaults['plugin']
     *
     * @var string|null
     */
    private ?string $plugin;

    /**
     * Route::defaults['prefix']
     *
     * @var mixed
     */
    private mixed $prefix;

    /**
     * Route::defaults['controller']
     *
     * @var string|null
     */
    private ?string $controller;

    /**
     * Route::defaults['action']
     *
     * @var string|null
     */
    private ?string $action;

    /**
     * Route::defaults['_method']
     *
     * @var array
     */
    private array $methods = [];

    /**
     * Route::template
     *
     * @var string|null
     */
    private ?string $template;

    /**
     * The fully qualified namespace of the controller
     *
     * @var string|null
     */
    private ?string $controllerFqn = null;

    /**
     * The model this route is associated with.
     *
     * @var \MixerApi\Core\Model\Model|null
     */
    private ?Model $model = null;

    /**
     * @param \Cake\Routing\Route\Route $route CakePHP Route instance
     * @param \Cake\Core\Configure|null $cakeConfigure CakePHP Configure class, if null an instance will be created.
     */
    public function __construct(private Route $route, private ?Configure $cakeConfigure = null)
    {
        $defaults = (array)$route->defaults;

        if (isset($defaults['_method']) && !is_array($defaults['_method'])) {
            $methods = explode(', ', $defaults['_method']);
        } else {
            $methods = $defaults['_method'] ?? [];
        }

        $this->cakeConfigure = $cakeConfigure ?? new Configure();

        $this
            ->setRoute($route)
            ->setTemplate($route->template)
            ->setName($route->getName())
            ->setPlugin($defaults['plugin'] ?? null)
            ->setPrefix($defaults['prefix'] ?? null)
            ->setController($defaults['controller'] ?? null)
            ->setAction($defaults['action'] ?? null)
            ->setMethods($methods);

        $fqn = $this->findControllerFqn();
        if ($fqn) {
            $this->setControllerFqn($fqn);
        }
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
     * @return string|null
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
     * @return mixed
     */
    public function getPrefix(): mixed
    {
        return $this->prefix;
    }

    /**
     * @param mixed $prefix The routing prefix, for example "Admin" when controller is in App\Controller\Admin
     * @return $this
     */
    public function setPrefix(mixed $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string|null
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
     * @return string|null
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
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action The controller method
     * @return $this
     */
    public function setAction(?string $action)
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
     * @return string|null
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
            explode('/', $this->template),
        );

        return implode('/', $pieces);
    }

    /**
     * Returns an instance of the object if the FQN exists, otherwise returns null.
     *
     * @return \Cake\Controller\Controller|null
     * @throws \ReflectionException
     */
    public function getControllerInstance(): ?Controller
    {
        if ($this->controllerFqn && class_exists($this->controllerFqn)) {
            $controller = (new ReflectionClass($this->controllerFqn))->newInstanceWithoutConstructor();

            return $controller instanceof Controller ? $controller : null;
        }

        return null;
    }

    /**
     * Returns the FQN of the controller or null if the controller cannot be found.
     *
     * @return string|null
     */
    private function findControllerFqn(): ?string
    {
        if (empty($this->controller)) {
            return null;
        }

        $app = $this->cakeConfigure::read('App.namespace');
        $fqn = $this->plugin ? str_replace('/', '\\', $this->plugin) . '\\' : $app . '\\';
        $fqn .= 'Controller\\';
        $fqn .= $this->prefix ? str_replace('/', '\\', $this->prefix) . '\\' : '';

        if (class_exists($fqn . $this->controller . 'Controller')) {
            return $fqn . $this->controller . 'Controller';
        } elseif (class_exists($fqn . Inflector::camelize($this->controller) . 'Controller')) {
            return $fqn . Inflector::camelize($this->controller) . 'Controller';
        }

        return null;
    }
}
