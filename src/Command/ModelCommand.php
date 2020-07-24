<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Routing\Router;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Utility\DataTypeConversion;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * Class ModelCommand
 *
 * @package SwaggerBake\Command
 */
class ModelCommand extends Command
{
    use CommandTrait;

    /**
     * @param \Cake\Console\ConsoleOptionParser $parser ConsoleOptionParser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('SwaggerBake Model Checker')
            ->addOption('prefix', [
                'help' => 'The route prefix (uses value in configuration by default)',
            ]);

        return $parser;
    }

    /**
     * List Cake Entities that can be added to Swagger. Prints to console.
     *
     * @param \Cake\Console\Arguments $args Arguments
     * @param \Cake\Console\ConsoleIo $io ConsoleIo
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->loadConfig();

        $io->hr();
        $io->out('| SwaggerBake is checking your models...');
        $io->hr();

        $config = new Configuration();
        ValidateConfiguration::validate($config);

        if (!empty($args->getOption('prefix'))) {
            $config->set('prefix', $args->getOption('prefix'));
        }

        $cakeRoute = new CakeRoute(new Router(), $config);
        $cakeModel = new CakeModel($cakeRoute, $config);
        $entities = $cakeModel->getEntityDecorators();

        if (empty($entities)) {
            $io->out();
            $io->warning('No models were found that are associated with: ' . $config->getPrefix());
            $io->out('Have you added RESTful routes? Do you have models associated with those routes?');
            $io->out();
            $this->abort();
        }

        $header = ['Attribute','Data Type', 'Swagger Type','Default','Primary Key'];

        foreach ($entities as $entity) {
            $io->out('- ' . $entity->getName());
            $output = [$header];
            foreach ($entity->getProperties() as $property) {
                $output[] = [
                    $property->getName(),
                    $property->getType(),
                    DataTypeConversion::toType($property->getType()),
                    $property->getDefault(),
                    $property->isPrimaryKey() ? 'Y' : '',
                ];
            }
            $io->helper('table')->output($output);
            $io->out("\r\n");
        }
    }
}
