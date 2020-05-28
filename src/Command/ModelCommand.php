<?php
declare(strict_types=1);

namespace SwaggerBake\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Routing\Router;
use SwaggerBake\Lib\CakeModel;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Utility\DataTypeConversion;
use SwaggerBake\Lib\Utility\ValidateConfiguration;

/**
 * Class ModelCommand
 * @package SwaggerBake\Command
 */
class ModelCommand extends Command
{
    /**
     * List Cake Entities that can be added to Swagger. Prints to console.
     *
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->hr();
        $io->out("| SwaggerBake is checking your models...");
        $io->hr();

        $config = new Configuration();
        ValidateConfiguration::validate($config);

        $cakeRoute = new CakeRoute(new Router(), $config);
        $cakeModel = new CakeModel($cakeRoute, $config);
        $entities = $cakeModel->getEntityDecorators();

        if (empty($entities)) {
            $io->out();
            $io->warning('No models were found that are associated with: ' . $config->getPrefix());
            $io->out("Have you added RESTful routes? Do you have models associated with those routes?");
            $io->out();
            return;
        }

        $header = ['Attribute','Data Type', 'Swagger Type','Default','Primary Key'];

        foreach ($entities as $entity) {
            $io->out('- ' . $entity->getName());
            $output = [$header];
            foreach ($entity->getAttributes() as $attribute) {
                $output[] = [
                    $attribute->getName(),
                    $attribute->getType(),
                    DataTypeConversion::convert($attribute->getType()),
                    $attribute->getDefault(),
                    $attribute->isPrimaryKey() ? 'Y' : '',
                ];
            }
            $io->helper('table')->output($output);
            $io->out("\r\n");
        }
    }
}
