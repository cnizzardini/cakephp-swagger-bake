<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Cake\Routing\Router;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;

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
     * @param \SwaggerBake\Lib\Configuration|null $config Configuration
     * @param \SwaggerBake\Lib\Route\RouteScanner|null $routeScanner RouteScanner
     */
    public function __construct(
        private ?Configuration $config = null,
        private ?RouteScanner $routeScanner = null,
    ) {
        $this->config = $config ?? new Configuration();

        $this->routeScanner = $routeScanner ?? new RouteScanner(new Router(), $this->config);
    }

    /**
     * Creates an instance of Swagger
     *
     * @return \SwaggerBake\Lib\Swagger
     * @throws \ReflectionException
     */
    public function create(): Swagger
    {
        return new Swagger(new ModelScanner($this->routeScanner, $this->config), $this->config);
    }
}
