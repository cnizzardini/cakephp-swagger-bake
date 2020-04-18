<?php
/**
 * @var string $prefix: The relative path that SwaggerBake will scan for your APIs routes (e.g. `/api/`)
 *
 * @var string $yml: The YML file from step 1 (generally `/config/swagger.yml`).
 *
 * @var string $json: A web accessible output file relative to your projects `/` root (generally `/webroot/swagger.json`).
 *
 * @var string $webPath: The path browsers will use to access the JSON file (generally `/swagger.json`).
 *
 * @var bool $hotReload: Regenerate swagger when Swagger UI page is reloaded. This only works if you are using the
 * built-in Swagger UI.
 *
 * @var string $docType: Options are swagger and redoc, defaults: swagger
 *
 * @var array $namespaces: Can be used if your controllers or entities exist in non-standard namespace such as a plugin
 */
return [
    'SwaggerBake' => [
        'prefix' => '/api',
        'yml' => '/config/swagger.yml',
        'json' => '/webroot/swagger.json',
        'webPath' => '/swagger.json',
        'hotReload' => false,
        'docType' => 'swagger',
        'namespaces' => [
            'controllers' => ['\App\\'],
            'entities' => ['\App\\'],
            'tables' => ['\App\\']
        ]
    ]
];


