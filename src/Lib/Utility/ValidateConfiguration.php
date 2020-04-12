<?php


namespace SwaggerBake\Lib\Utility;


use Cake\Core\Configure;
use Cake\Routing\Router;
use LogicException;
use SwaggerBake\Lib\CakeRoute;
use SwaggerBake\Lib\Configuration;

class ValidateConfiguration
{
    public static function validate()
    {
        $config = new Configuration();
        $ymlFile = $config->getYml();

        if (empty($ymlFile) || !strstr($ymlFile, '.yml')) {
            throw new LogicException('Yml file is required');
        }

        if (!file_exists($ymlFile)) {
            throw new LogicException('Yml file not found, try specifying full path to file');
        }

        $prefix = $config->getPrefix();

        if (empty($prefix)) {
            throw new LogicException('Prefix is required');
        }

        $output = $config->getJson();

        if (!file_exists($output) && !touch($output)) {
            throw new LogicException(
                'Unable to create swagger file. Try creating an empty file first or checking permissions'
            );
        }
    }
}