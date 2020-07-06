<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App;

use Bake\Command\EntryCommand;
use Cake\Console\CommandCollection;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication
{
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        return $middleware;
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
}
