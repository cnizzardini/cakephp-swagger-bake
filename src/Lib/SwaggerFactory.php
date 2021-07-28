<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Routing\Router;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * Class SwaggerFactory
 *
 * @package SwaggerBake\Lib\Factory
 *
 * Creates an instance of SwaggerBake\Lib\Swagger
 */
class SwaggerFactory
{
    private Configuration $config;

    private RouteScanner $routeScanner;

    /**
     * @param \SwaggerBake\Lib\Configuration|null $config Configuration
     * @param \SwaggerBake\Lib\Route\RouteScanner|null $routeScanner RouteScanner
     */
    public function __construct(?Configuration $config = null, ?RouteScanner $routeScanner = null)
    {
        $this->config = $config ?? new Configuration();
        ValidateConfiguration::validate($this->config);

        $this->routeScanner = $routeScanner ?? new RouteScanner(new Router(), $this->config);
    }

    /**
     * Creates an instance of Swagger
     *
     * @return \SwaggerBake\Lib\Swagger
     */
    public function create(): Swagger
    {
        $routes = $this->routeScanner->getRoutes();

        if (empty($routes)) {
            throw new SwaggerBakeRunTimeException(
                'No restful routes were found for your prefix `' . $this->config->getPrefix() . '`. ' .
                'Try adding restful routes to your `config/routes.php`.'
            );
        }

        return new Swagger(new ModelScanner($this->routeScanner, $this->config));
    }
}
