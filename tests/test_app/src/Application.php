<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App;

use Bake\Command\EntryCommand;
use Cake\Console\CommandCollection;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication
{
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        return $middleware->add(new RoutingMiddleware($this))->add(new BodyParserMiddleware());
    }

    public function bootstrap(): void
    {
        $this->addPlugin('Bake');
        $this->addPlugin('SwaggerBake');
    }

    public function console(CommandCollection $commands): CommandCollection
    {
        $commands->add('bake', EntryCommand::class);
        return $commands;
    }

    public function routes(RouteBuilder $routes): void
    {
        $routes->scope('/', function (RouteBuilder $builder) {
            $builder->fallbacks();
            $builder->setExtensions(['json','xml']);
            $builder->resources('Departments');
            $builder->connect('/', [
                'plugin' => 'SwaggerBake', 'controller' => 'Swagger', 'action' => 'index'
            ]);
        });
        parent::routes($routes);
    }
}
