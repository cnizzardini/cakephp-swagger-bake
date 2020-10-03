<?php
/**
 * ################################
 * # REQUIRED SETTINGS:
 * ################################
 *
 * @var string $prefix: The route scope that SwaggerBake will scan for your APIs routes (e.g. `/api/`)
 *
 * @var string $yml: A base Swagger YML file, see example in assets (e.g. `/config/swagger.yml`).
 *
 * @var string $json: Web accessible file path the JSON file is written to (e.g. `/webroot/swagger.json`).
 *
 * @var string $webPath: The URL browsers will use to access the JSON file (e.g. `/swagger.json`).
 *
 * ################################
 * # RECOMMENDED SETTINGS:
 * ################################
 *
 * @var bool $hotReload: Regenerate swagger on page reloaded. This only works if you are using the built-in Swagger UI.
 *      Using your applications debug value is recommended as an easy way to define this.
 *      Default: false
 *
 * ################################
 * # OPTIONAL SETTINGS:
 * ################################
 *
 * @var string[] $requestAccepts: Array of mime types accepted. Can be used if your application accepts JSON, XML etc...
 *      Default: application/x-www-form-urlencoded
 *
 * @var string[] $responseContentTypes: Array of mime types returned. Can be used if your application returns XML etc...
 *      Default: application/json
 *
 * @var string $docType: Options are swagger and redoc.
 *      Default: swagger
 *
 * @var string $exceptionSchema: The name of your Exception schema in components > schemas defined in your swagger.yml.
 *      Default: Exception.
 *
 * @var array[] $namespaces: Array of namespaces. Useful if your controllers or entities exist in non-standard
 *      namespace such as a plugin. This was mostly added to aid in unit testing, but there are cases where controllers
 *      may exist in a plugin namespace etc...
 *      Default: \App\\
 */
return [
    'SwaggerBake' => [
        'prefix' => '/your-relative-api-url',
        'yml' => '/config/swagger.yml',
        'json' => '/webroot/swagger.json',
        'webPath' => '/swagger.json',
        'hotReload' => \Cake\Core\Configure::read('debug'),
        /** optional configurations below:  **/
        /*
        'requestAccepts' => [
            'application/x-www-form-urlencoded',
            'application/json',
            'application/xml'
        ],
        'responseContentTypes' => [
            'application/json',
            'application/xml'
        ],
        'docType' => 'swagger',
        'exceptionSchema' => 'Exception',
        'namespaces' => [
            'controllers' => ['\App\\'],
            'entities' => ['\App\\'],
            'tables' => ['\App\\']
        ]
        */
    ]
];


