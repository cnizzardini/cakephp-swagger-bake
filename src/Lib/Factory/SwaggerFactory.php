<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Factory;

use Cake\Routing\Router;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
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
     * @var \SwaggerBake\Lib\CakeRoute
     */
    private $cakeRoute;

    /**
     * @param \SwaggerBake\Lib\Configuration|null $config Configuration
     * @param \SwaggerBake\Lib\CakeRoute|null $cakeRoute CakeRoute
     */
    public function __construct(?Configuration $config = null, ?CakeRoute $cakeRoute = null)
    {
        $this->config = $config ?? new Configuration();
        ValidateConfiguration::validate($this->config);

        $this->cakeRoute = $cakeRoute ?? new CakeRoute(new Router(), $this->config);
    }

    /**
     * Creates an instance of Swagger
     *
     * @return \SwaggerBake\Lib\Swagger
     */
    public function create(): Swagger
    {
        $routes = $this->cakeRoute->getRoutes();

        if (empty($routes)) {
            throw new SwaggerBakeRunTimeException(
                'No restful routes were found for your prefix `' . $this->config->getPrefix() . '`. ' .
                'Try adding restful routes to your `config/routes.php`.'
            );
        }

        return new Swagger(new CakeModel($this->cakeRoute, $this->config));
    }
}
