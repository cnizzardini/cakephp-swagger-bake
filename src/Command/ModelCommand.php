<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Routing\Router;
use ReflectionClass;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ModelDecorator;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\Route\RouteScanner;
use SwaggerBake\Lib\Utility\DataTypeConversion;

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
            ->addOption('config', [
                'help' => 'Configuration (defaults to config/swagger_bake). Example: OtherApi.swagger_bake',
                'default' => 'swagger_bake',
            ])
            ->addOption('prefix', [
                'help' => 'The route prefix (uses value in configuration by default)',
            ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): void
    {
        $this->loadConfig($args);

        $io->hr();
        $io->out('| SwaggerBake is checking your models...');
        $io->hr();

        $config = new Configuration();

        $prefix = $args->getOption('prefix');
        if (!empty($prefix) && is_string($prefix)) {
            $config->setPrefix($prefix);
        }

        $routeScanner = new RouteScanner(new Router(), $config);
        $modelScanner = new ModelScanner($routeScanner, $config);
        $models = $modelScanner->getModelDecorators();

        if (empty($models)) {
            $io->out();
            $io->warning('No models were found that are associated with: ' . $config->getPrefix());
            $io->out('Have you added RESTful routes? Do you have models associated with those routes?');
            $io->out();
            $this->abort();
        }

        usort($models, function (ModelDecorator $a, ModelDecorator $b) {
            return strcasecmp($a->getModel()->getSchema()->name(), $b->getModel()->getSchema()->name());
        });

        $header = [
            'Attribute',
            'Data Type',
            'Swagger Type',
            'Default',
            'Primary Key',
            'Mass Assignment',
            'Hidden',
        ];

        foreach ($models as $model) {
            $io->out('- ' . (new ReflectionClass($model->getModel()->getEntity()))->getShortName());
            $output = [$header];
            foreach ($model->getModel()->getProperties() as $property) {
                $output[] = [
                    $property->getName(),
                    $property->getType(),
                    DataTypeConversion::toType($property->getType()),
                    $property->getDefault(),
                    $property->isPrimaryKey() ? 'Y' : '',
                    $property->isAccessible() ? 'Y' : '',
                    $property->isHidden() ? 'Y' : '',
                ];
            }
            $io->helper('table')->output($output);
            $io->out("\r\n");
        }
    }
}
