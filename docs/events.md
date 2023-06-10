# Supported Events

Swagger Bake uses the [CakePHP Event System](https://book.cakephp.org/5/en/core-libraries/events.html)

| Description                                                 | Event                                               | 
|-------------------------------------------------------------|-----------------------------------------------------|
| Dispatched each time an OpenAPI Path > Operation is created | [SwaggerBake.Operation.created](#operation-created) |
| Dispatched each time an OpenAPI Path is created             | [SwaggerBake.Path.created](#path-created)           |
| Dispatched each time an OpenAPI Schema is created           | [SwaggerBake.Schema.created](#schema-created)       |
| Dispatched during initialization phase on SwaggerBake       | [SwaggerBake.initialize](#initialize)               |
| Dispatched before SwaggerBake outputs OpenAPI JSON          | [SwaggerBake.beforeRender](#before-render)          |

### Operation Created

The `SwaggerBake.Operation.created` is dispatched each time a new `SwaggerBake\Lib\OpenApi\Operation` is created. 
Here is an example of modifying a summary and adding OpenAPI security:

```php
EventManager::instance()
    ->on('SwaggerBake.Operation.created', function (Event $event) {
        /** @var \SwaggerBake\Lib\OpenApi\Operation $operation */
        $operation = $event->getSubject();
        $operation
            ->setSummary('My new summary.')
            ->setSecurity([
                (new \SwaggerBake\Lib\OpenApi\PathSecurity('BearerAuth'))
            ]);
    });
```

### Path Created

The `SwaggerBake.Path.created` is dispatched each time a new`SwaggerBake\Lib\OpenApi\Path` is created. Here is an
example of modifying a summary:
```php
EventManager::instance()
    ->on('SwaggerBake.Path.created', function (Event $event) {
        /** @var \SwaggerBake\Lib\OpenApi\Path $path */
        $path = $event->getSubject();
        $path->setSummary('My new summary');
    });
```

### Schema Created

The `SwaggerBake.Schema.created` is dispatched each time a new `SwaggerBake\Lib\OpenApi\Schema` instance is 
created. Here is an example of modifying a title:

```php
EventManager::instance()
    ->on('SwaggerBake.Schema.created', function (Event $event) {
        /** @var \SwaggerBake\Lib\OpenApi\Schema $schema */
        $schema = $event->getSubject();
        $schema->setTitle('My new title');
    });
```

### Initialize

The `SwaggerBake.initialize` is dispatched once, just before `SwaggerBake\Lib\Swagger` begins building OpenAPI 
from your routes, models, and attributes.

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

### Before Render

The `SwaggerBake.beforeRender` is dispatched once, just before `SwaggerBake\Lib\Swagger` converts data to an 
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
