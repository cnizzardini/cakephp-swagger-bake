# SwaggerBake plugin for CakePHP4

`Note: This is an alpha stage plugin and prone to lots of changes right now`

A delightfully tasty tool for generating Swagger documentation with OpenApi 3.0.0 schema. This plugin automatically 
builds swagger JSON for you with minimal configuration and effort. It operates on convention and assumes your 
application is [RESTful](https://book.cakephp.org/4/en/development/rest.html). Swagger UI 3.25.0 comes pre-installed 
with this plugin.

### Installation

SwaggerBake requires CakePHP4 and a few dependencies that will be automatically installed via composer.

```
composer require cnizzardini/cakephp-swagger-bake
```

Add the plugin to `Application.php`:

```php
$this->addPlugin('SwaggerBake');
```

### Basic Usage

Get going in just four, yep FOUR, easy steps:

Step 1: Create a base swagger.yml file in `config\swagger.yml`. An example is provided [here](assets/swagger.yml). 

Step 2: Create a `config/swagger_bake.php` file. An example is provided [here](assets/swagger_bake.php) with further 
explanation of the configuration options.

Step 3: Use the `swagger bake` command to generate your swagger documentation. 

```sh
bin/cake swagger bake
```

Step 4: Create a route for SwaggerBake in `config/routes.php`

```php
$builder->connect('/api', ['controller' => 'Swagger', 'action' => 'index', 'plugin' => 'SwaggerBake']);
```

Using the above example you should now see your swagger documentation after browsing to http://your-project/api

##### Hot Reload Swagger JSON

You can enable hot reloading. This setting re-generates swagger.json on each reload of Swagger UI. Simply set 
`hotReload` equal to `true` in your `config/swagger_bake.php` file. This is not recommended for production.

### DocBlock Parsing

SwaggerBake will parse some of your doc blocks for information. The first line of Doc Blocks above Controller Actions 
are used for the Path Summary. 

### Annotations

SwaggerBake provides some optional Annotations for additional functionality.

#### `@SwagPaginator`
Use @SwagPaginator on Controller actions that use the 
[CakePHP Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html). This will add the following 
query parameters to Swagger:
- page
- limit
- rows
- sort
- direction

```php
/**
 * @SwagPaginator
 */
public function index()
{
    $employees = $this->paginate($this->Employees);
    $this->set(compact('employees'));
    $this->viewBuilder()->setOption('serialize', ['employees']);
}
```

### Extensibility

There are several options to extend the functionality of SwaggerBake

##### Using Your Own SwaggerUI

You may use your own swagger install in lieu of the version that comes with SwaggerBake. Simply don't add a custom 
route as indicated in step 4 of Basic Usage. In this case just reference the generated swagger.json with your own 
Swagger UI install.

##### Generate Swagger On Your Terms

If you want to hook the build process into some other portion of your application you can use the Swagger class to do
so. Check out the [Bake Command](src/Command/BakeCommand.php) for a use-case. Once you've constructed an instance of 
`Swagger`, simply call `$swagger->toString()` to get the JSON or `$swagger->toArray()` if want you to view/modify the 
array first. Here's an example:

```php
$swagger = (new \SwaggerBake\Lib\Factory\SwaggerFactory())->create();
$swagger->toArray(); # returns swagger array
$swagger->toString(); # returns swagger json
```

### Console Commands

In addition to `swagger bake` these console helpers provide insight into how your Swagger documentation is generated.

#### `swagger routes` 
Generates a list of routes that will be added to your swagger documentation. It uses the `prefix` 
config from your `config/swagger_bake.php` file.

```sh
bin/cake swagger routes
```

#### `swagger models` 
Generates a list of models that will be added to your swagger documentation. These models must have Cake\ORM\Entities 
and exist in your App\Controller namespace following CakePHP conventions. Entity attributes marked as hidden in your 
App\Model\Entity classes will be ignored. It only retrieves models that are resources in your route prefix.

```sh
bin/cake swagger models
```

### OpenApi 3 Specification Support

SwaggerBake builds on your existing swagger.yml definitions. This allows you to add custom definitions. SwaggerBake 
will not overwrite paths or schemas that already exist in your definition file. 

Not every portion of the OpenApi 3 spec is supported just yet, but I am working on that. Please create feature request 
issues if you notice something missing.

SwaggerBake has been developed for application/json and has not been tested with application/xml.

### Supported Versions

This is built for CakePHP 4.x only.

| Version  | Supported | Unit Tests | Notes |
| ------------- | ------------- | ------------- | ------------- |
| 4.0 | Yes  | Yes |  |

### Reporting Issues

This is a new library so please take some steps before reporting issues. You can copy & paste the JSON SwaggerBake 
outputs into https://editor.swagger.io/ which will automatically convert the JSON into YML and display potential 
schema issues.

Please included the following in your issues a long with a brief description:

- Steps to Reproduce
- Actual Outcome
- Expected Outcome

Feature requests are welcomed.

### Contribute

Send pull request to help improve this library. You can include SwaggerBake in your primary Cake project as a 
local source to make developing easier:

- Make a clone of this repository

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

### Unit Tests

```sh
vendor/bin/phpunit
```