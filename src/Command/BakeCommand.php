<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * Class BakeCommand
 *
 * @package SwaggerBake\Command
 */
class BakeCommand extends Command
{
    /**
     * Writes a swagger.json file
     *
     * @param \Cake\Console\Arguments $args Arguments
     * @param \Cake\Console\ConsoleIo $io ConsoleIo
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('Running...');

        $config = new Configuration();
        ValidateConfiguration::validate($config);
        $output = $config->getJson();

        $swagger = (new SwaggerFactory())->create();
        foreach ($swagger->getOperationsWithNoHttp20x() as $operation) {
            triggerWarning('Operation ' . $operation->getOperationId() . ' does not have a HTTP 20x response');
        }

        $swagger->writeFile($output);

        if (!file_exists($output)) {
            $io->out("<error>Error Creating File: $output</error>");

            return;
        }

        $io->out("<success>Swagger File Created: $output</success>");
    }
}
