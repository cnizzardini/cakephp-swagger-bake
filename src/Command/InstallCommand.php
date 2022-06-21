<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use SwaggerBake\Lib\Exception\InstallException;
use SwaggerBake\Lib\Service\InstallerService;

class InstallCommand extends Command
{
    /**
     * @param \SwaggerBake\Lib\Service\InstallerService $service InstallerService
     */
    public function __construct(private InstallerService $service)
    {
        parent::__construct();
    }

    /**
     * @param \Cake\Console\ConsoleOptionParser $parser ConsoleOptionParser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('SwaggerBake Installer');

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
        $io->hr();
        $io->out('| SwaggerBake Install');
        $io->hr();

        $continue = $io->ask(
            'If your API exists in a plugin or you have some other non-standard setup, please follow ' .
            'the manual installation steps. Do you want to continue?',
            'Y'
        );

        if (strtoupper($continue) !== 'Y') {
            $io->out('Exiting install');
            $this->abort();
        }

        $path = $io->ask('What is your APIs path prefix e.g. `/` or `/api`?');

        $installComplete = $skipErrors = false;
        do {
            try {
                $installComplete = $this->service->install($path, $skipErrors);
            } catch (InstallException $e) {
                if ($e->getQuestion() === null) {
                    $io->out('<error>' . $e->getMessage() . '</error>');
                    $this->abort();
                }
                $skipErrors = strtoupper($io->ask($e->getQuestion(), 'Y')) === 'Y';
            }
        } while ($installComplete != true);

        $io->out("Just a few more steps:");
        $io->out("1. Load the swagger_bake.php config in your config/bootstrap.");
        $io->out("2. Add a route in your config/routes.php to the SwaggerBake controller.");

        $io->success('Installation Complete!');
    }
}
