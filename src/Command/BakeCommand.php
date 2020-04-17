<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;

class BakeCommand extends Command
{
    /**
     * Writes a swagger.json file
     *
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out("Running...");

        $config = new Configuration();
        $output = $config->getJson();

        $swagger = (new SwaggerFactory())->create();
        $swagger->writeFile($output);

        $io->out("<success>Swagger File Created: $output</success>");
    }
}
