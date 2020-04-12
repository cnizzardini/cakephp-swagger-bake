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
 * @class ModelCommand
 * @package SwaggerBake
 */
class ModelCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out("Running...");

        ValidateConfiguration::validate();

        $config = new Configuration();

        $cakeRoute = new CakeRoute(new Router(), $config);
        $cakeModel = new CakeModel($cakeRoute, $config);
        $models = $cakeModel->getModels();

        $header = ['Attribute','Data Type', 'Swagger Type','Default','Primary Key'];

        foreach ($models as $model) {
            $io->out('- ' . $model->getName());
            $output = [$header];
            foreach ($model->getAttributes() as $attribute) {
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
