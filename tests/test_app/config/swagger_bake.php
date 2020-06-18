<?php
return [
    'SwaggerBake' => [
        'prefix' => '/',
        'yml' => '/config/swagger.yml',
        'json' => '/webroot/swagger.json',
        'webPath' => '/swagger.json',
        'hotReload' => false,
        'namespaces' => [
            'controllers' => ['\App\\'],
            'entities' => ['\App\\'],
        ]
    ]
];
