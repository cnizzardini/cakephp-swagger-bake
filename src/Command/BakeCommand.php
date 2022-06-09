<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Service\OpenApiBakerService;
use SwaggerBake\Lib\SwaggerFactory;

/**
 * Class BakeCommand
 *
 * @package SwaggerBake\Command
 */
class BakeCommand extends Command
{
    use CommandTrait;

    /**
     * @param \SwaggerBake\Lib\Service\OpenApiBakerService $service OpenApiBakerService
     */
    public function __construct(private OpenApiBakerService $service)
    {
        parent::__construct();
    }

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
     * @throws \ReflectionException
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->loadConfig($args);
        $io->out('Running...');
        $swagger = (new SwaggerFactory())->create();

        /** @var string $output */
        $output = $args->getOption('output') ?? $swagger->getConfig()->getJson();
        try {
            $result = $this->service->bake($swagger, $output);
            $io->out("<success>Swagger File Created: $result</success>");

            $warnings = $this->service->getWarnings();
            if (count($warnings) > 0) {
                $io->out('<warning>' . count($warnings) . ' warning(s) were detected</warning>');
                foreach ($warnings as $warning) {
                    $io->out("<warning>$warning</warning>");
                }
            }
        } catch (SwaggerBakeRunTimeException $e) {
            $io->out('<error>' . $e->getMessage() . '</error>');
        }
    }
}
