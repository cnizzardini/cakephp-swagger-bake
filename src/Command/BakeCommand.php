<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Factory\SwaggerFactory;

/**
 * Class BakeCommand
 *
 * @package SwaggerBake\Command
 */
class BakeCommand extends Command
{
    use CommandTrait;

    /**
     * @param \Cake\Console\ConsoleOptionParser $parser ConsoleOptionParser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('SwaggerBake OpenAPI JSON Generator')
            ->addOption('config', [
                'help' => 'Configuration (defaults to config/swagger_bake). Example: OtherApi.swagger_bake',
                'default' => 'swagger_bake',
            ])
            ->addOption('output', [
                'help' => 'Full path for OpenAPI json file (defaults to config value for SwaggerBake.json)',
            ]);

        return $parser;
    }

    /**
     * Writes a swagger.json file
     *
     * @param \Cake\Console\Arguments $args Arguments
     * @param \Cake\Console\ConsoleIo $io ConsoleIo
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->loadConfig($args);

        $io->out('Running...');

        $config = new Configuration();

        /** @var string $output */
        $output = $args->getOption('output') ?? $config->getJson();
        if (empty($output) || !is_string($output)) {
            $io->out('<error>Output must be a string in file path format</error>');
            $this->abort();
        }

        $swagger = (new SwaggerFactory())->create();
        foreach ($swagger->getOperationsWithNoHttp20x() as $operation) {
            triggerWarning('Operation ' . $operation->getOperationId() . ' does not have a HTTP 20x response');
        }

        $swagger->writeFile($output);

        if (!file_exists($output)) {
            $io->out("<error>Error Creating File: $output</error>");
            $this->abort();
        }

        $io->out("<success>Swagger File Created: $output</success>");
    }
}
