<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class InstallCommand
 *
 * @package SwaggerBake\Command
 */
class InstallCommand extends Command
{
    /**
     * @param \Cake\Console\ConsoleOptionParser $parser ConsoleOptionParser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('SwaggerBake Installer')
            ->addOption('config_test', [
                'help' => 'For testing purposes only (don\'t use)',
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
        $io->hr();
        $io->out('| SwaggerBake Install');
        $io->hr();

        $io->info('This will create, but not overwrite config/swagger.yml and config/swagger_bake.php');

        $io->out(
            'If your API exists in a plugin or you have some other non-standard setup, please follow ' .
            'the manual installation steps.'
        );

        if (strtoupper($io->ask('Continue?', 'Y')) !== 'Y') {
            return;
        }

        $configDir = $args->getOption('config_test') ?? CONFIG;

        $assets = __DIR__ . DS . '..' . DS . '..' . DS . 'assets';
        if (!dir($assets)) {
            $io->error('Unable to locate assets directory, please install manually');
            $this->abort();
        }

        if (file_exists($configDir . 'swagger.yml') || file_exists($configDir . 'swagger_bake.php')) {
            $answer = $io->ask('The installer found existing SwaggerBake config files. Overwrite?', 'Y');
            if (strtoupper($answer) !== 'Y') {
                $this->abort();
            }
        }

        $swaggerYml = $configDir . 'swagger.yml';
        if (!copy("$assets/swagger.yml", $swaggerYml)) {
            $io->error('Unable to copy swagger.yml, check permissions');
            $this->abort();
        }

        $path = trim($io->ask('What is your APIs path prefix (e.g. /api)'));
        if (empty($path) || !filter_var('http://localhost' . $path, FILTER_VALIDATE_URL)) {
            $io->error('A valid API path prefix is required');
            $this->abort();
        }

        if (!copy("$assets/swagger_bake.php", $configDir . 'swagger_bake.php')) {
            $io->error('Unable to copy swagger_bake.php, check permissions');
            $this->abort();
        }

        $contents = file_get_contents($swaggerYml);
        $contents = str_replace('YOUR-SERVER-HERE', $path, $contents);
        file_put_contents($swaggerYml, $contents);

        $contents = file_get_contents($configDir . 'swagger_bake.php');
        $contents = str_replace('/your-relative-api-url', $path, $contents);
        file_put_contents($configDir . 'swagger_bake.php', $contents);

        $io->out('Now just add a route in your config/routes.php for SwaggerUI and you\'re ready to go!');

        $io->success('Installation Complete!');
    }
}
