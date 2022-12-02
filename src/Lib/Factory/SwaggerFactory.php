<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Factory;

use Cake\Routing\Router;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Swagger;
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
    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @var \SwaggerBake\Lib\Route\RouteScanner
     */
    private $routeScanner;

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
        return new Swagger(new ModelScanner($this->routeScanner, $this->config));
    }
}
