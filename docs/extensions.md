# SwaggerBake Extensions

Extensions to SwaggerBake can be added through the use of events. See CakeSearch 
for an example. You may submit extensions as PRs to this project or simply create 
extended functionality within your own project using the event system.

## Supported Events

The `SwaggerBake.Operation.created` is dispatched each time a new [Operation](src/Lib/OpenApi/Operation.php) is 
created. Simply listen for the event: 

```php
EventManager::instance()
    ->on('SwaggerBake.Operation.created', function (Event $event) {
        /** @var \SwaggerBake\Lib\OpenApi\Operation $operation */
        $operation = $event->getSubject();
    });
```

The `SwaggerBake.Schema.created` is dispatched each time a new [Schema](src/Lib/OpenApi/Schema.php) instance is 
created. Simply listen for the event: 

```php
EventManager::instance()
    ->on('SwaggerBake.Schema.created', function (Event $event) {
        /** @var \SwaggerBake\Lib\OpenApi\Schema $schema */
        $schema = $event->getSubject();
    });
```

The `SwaggerBake.beforeRender` is dispatched once, just before [Swagger](src/Lib/Swagger.php) converts data to an 
OpenAPI array or json. 

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

## Adding your Extension to the SwaggerBake project

1. Your extension must implement ExtensionInterface. Read the interfaces comments and refer to the CakeSearch 
extension for additional insight.

2. Add your extension to the `EXTENSIONS` constant in `src/Lib/ExtensionLoader.php`.

3. Create necessary unit tests `tests/TestCase/Lib/Extension`.

4. Submit your PR!
