<?php

namespace SwaggerBake\Test\TestCase\Helper;

use SwaggerBake\Lib\Configuration;

trait ConfigurationHelperTrait
{
    public static function createConfiguration()
    {
        $configs  = [
            'prefix' => '/your-relative-api-url',
            'yml' => '/config/swagger.yml',
            'json' => '/webroot/swagger.json',
            'webPath' => '/swagger.json',
        ];

        return new Configuration($configs, SWAGGER_BAKE_TEST_APP);
    }
}