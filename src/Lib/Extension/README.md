# SwaggerBake Extensions

Extensions to SwaggerBake can be added through the use of events. See CakeSearch 
for an example. You may submit extensions as PRs to this project or simply create 
extended functionality within your own project using the event system.

## Supported Events

The `SwaggerBake.Operation.created` is dispatched each time a new Operation is
created. Simply listen for the event: 

```php
EventManager::instance()
    ->on('SwaggerBake.Operation.created', function (Event $event) {
        // your code
    });
```

## Adding your Extension to the SwaggerBake project

1. Your extension must implement ExtensionInterface. Read the interfaces comments and refer to the CakeSearch 
extension for additional insight.

2. Add your extension to the `EXTENSIONS` constant in `src/Lib/ExtensionLoader.php`.

3. Create necessary unit tests `tests/TestCase/Lib/Extension`.

4. Submit your PR!
