# SwaggerBake plugin for CakePHP4

A delightfully tasty tool for generating Swagger documentation with OpenApi 3.0.0 schema. This plugin automatically 
builds swagger JSON for you with minimal configuration and effort. It operates on convention and assumes your 
application is [RESTful](https://book.cakephp.org/4/en/development/rest.html). Swagger UI 3.25.0 comes pre-installed 
with this plugin.

SwaggerBake will generate: 

- Path
    - Operation
        - OperationId
        - HTTP Method
        - Tags
        - Parameter
            - In (Path Only)
        - Response
- Component
    - Schema
        - Type
        - Property
            - Type

SwaggerBake builds on your existing swagger.yml definitions. This allows you to add custom definitions. SwaggerBake 
will not overwrite paths or schemas that already exist in your definition file. Read more on this in Basic Usage.

SwaggerBake does not currently generate all portions of the specification, but I have plans to generate: 

- Path
    - Operation
        - Summary *
        - Parameter
            - In (Query) ([Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html)) *
- Component
    - Schema
        - Required *
        - Property
            - Format *

`*` indicates desired development

SwaggerBake has been developed for application/json and has not been tested with application/xml.

## Installation

SwaggerBake requires CakePHP4 and a few dependencies that will be automatically installed via composer.

```
composer require cnizzardini/cakephp-swagger-bake
```

Add the plugin to `Application.php`:

```php
$this->addPlugin('SwaggerBake');
```

### Basic Usage

Get going into just four, yep FOUR, easy steps:

Step 1: Create a base swagger.yml file in `config\swagger.yml`. An example is provided in 
[assets/swagger.yml](assets/swagger.yml). SwaggerBake will append path operations to this file.

Step 2: Create a `config/swagger_bake.php` file. An example is provided in 
[assets/swagger_bake.php](config/swagger_bake.php). 

- prefix: The relative path that SwaggerBake will scan for your APIs routes (e.g. `/api/`)
- yml: The YML file from step 1 (generally `/config/swagger.yml`).
- json: A web accessible output file relative to your projects `/` root (generally `/webroot/swagger.json`).
- webPath: The path browsers will use to access the JSON file (generally `/swagger.json`).

Step 3: Use the `swagger bake` command to generate your swagger documentation. 

```sh
bin/cake swagger bake
```

Step 4: Create a route for SwaggerBake in `config/routes.php`

```php
$builder->connect('/api', ['controller' => 'Swagger', 'action' => 'index', 'plugin' => 'SwaggerBake']);
```

Using the above example you should now see your swagger documentation after browsing to http://your-project/api

### Extensibility

There are several options to extend the functionality of SwaggerBake

##### Using Your Own SwaggerUI

You may use your own swagger install in lieu of the version that comes with SwaggerBake. Simply don't add a custom 
route as indicated in step 4 of Basic Usage. In this case just reference the generated swagger.json with your own 
Swagger UI install.

##### Generate Swagger On Demand

If you want to hook the build process into some other portion of your application you can use the Swagger class to do
so. Check out the [Bake Command](src/Command/BakeCommand.php) for a use-case. Once you've constructed an instance of 
`Swagger`, simply call `$swagger->__toString()` to get the JSON or `$swagger->toArray()` if want you to view/modify the 
array first. Here's an example:

```php
$cakeRoute = new \SwaggerBake\Lib\CakeRoute(new \Cake\Routing\Router(), '/api');
$swagger = new \SwaggerBake\Lib\Swagger(
    '/config/swagger.yml',
    new \SwaggerBake\Lib\CakeModel($cakeRoute, '/api'),
);

$swagger->toArray(); # returns swagger array
$swagger->__toString(); # returns swagger json
```

### Console Commands

In addition to `swagger bake` these console helpers provide insight into how your Swagger documentation is generated.

`swagger routes` generates a list of routes that will be added to your swagger documentation. It uses the `prefix` 
config from your `config/swagger_bake.php` file.

```sh
bin/cake swagger routes
```

`swagger models` generates a list of models that will be added to your swagger documentation. These models must have 
Cake\ORM\Entities and exist in your App\Controller namespace following CakePHP conventions. Entity attributes marked 
as hidden in your App\Model\Entity classes will be ignored. It only retrieves models that are resources in your route 
prefix.

```sh
bin/cake swagger models
```

### Reporting Issues

This is a new library so please take some steps before reporting issues. You can copy & paste the JSON SwaggerBake 
outputs into https://editor.swagger.io/ which will automatically convert the JSON into YML and display potential 
schema issues.

Please included the following in your issues a long with a brief description:

- Steps to Reproduce
- Expected Outcome
- Actual Outcome

Feature requests are welcomed.

### Contribute

Send pull request to help improve this library. You can include SwaggerBake in your primary Cake project as a 
local source to make developing easier:

- Remove `cnizzardini\cakephp-swagger-bake` from your `composer.json`

- Add a paths repository to your `composer.json`
```
"repositories": [
    {
        "type": "path",
        "url": "/absolute/local-path-to/cakephp-swagger-bake",
        "options": {
          "symlink": true
        }
    }
]
```
- Run `composer require cnizzardini/cakephp-swagger-bake @dev`

Undo these steps when you're done. Read the full composer documentation on loading from path here: 
[https://getcomposer.org/doc/05-repositories.md#path](https://getcomposer.org/doc/05-repositories.md#path)

## Supported Versions

This is built for CakePHP 4.x only.

| Version  | Supported | Unit Tests | Notes |
| ------------- | ------------- | ------------- | ------------- |
| 4.0 | Yes  | @todo |  |

### Unit Tests

@todo 

```sh
vendor/bin/phpunit
```