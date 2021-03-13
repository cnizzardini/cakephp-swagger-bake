# Supported Events

The `SwaggerBake.Operation.created` is dispatched each time a new [Operation](https://github.com/cnizzardini/cakephp-swagger-bake/blob/master/src/Lib/OpenApi/Operation.php) is created. Simply listen for the event: 

```php
EventManager::instance()
    ->on('SwaggerBake.Operation.created', function (Event $event) {
        /** @var \SwaggerBake\Lib\OpenApi\Operation $operation */
        $operation = $event->getSubject();
    });
```

The `SwaggerBake.Schema.created` is dispatched each time a new [Schema](https://github.com/cnizzardini/cakephp-swagger-bake/blob/master/src/Lib/OpenApi/Schema.php) instance is created. Simply listen for the event: 

```php
EventManager::instance()
    ->on('SwaggerBake.Schema.created', function (Event $event) {
        /** @var \SwaggerBake\Lib\OpenApi\Schema $schema */
        $schema = $event->getSubject();
    });
```

The `SwaggerBake.initialize` is dispatched once, just before [Swagger](https://github.com/cnizzardini/cakephp-swagger-bake/blob/master/src/Lib/Swagger.php) begins building OpenAPI from your routes, models, and annotations.

```php
EventManager::instance()
    ->on('SwaggerBake.initialize', function (Event $event) {
        /** @var \SwaggerBake\Lib\Swagger $swagger */
        $swagger = $event->getSubject();
        $array = $swagger->getArray();
        $array['title'] = 'A new title';
        $swagger->setArray($array);
    });
```

The `SwaggerBake.beforeRender` is dispatched once, just before [Swagger](https://github.com/cnizzardini/cakephp-swagger-bake/blob/master/src/Lib/Swagger.php) converts data to an OpenAPI array or json. 

```php
EventManager::instance()
    ->on('SwaggerBake.beforeRender', function (Event $event) {
        /** @var \SwaggerBake\Lib\Swagger $swagger */
        $swagger = $event->getSubject();
        $array = $swagger->getArray();
        $array['title'] = 'A new title';
        $swagger->setArray($array);
    });
```