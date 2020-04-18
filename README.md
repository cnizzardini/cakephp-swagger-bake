# SwaggerBake plugin for CakePHP4

`Note: This is a release-candidate stage plugin`

A delightfully tasty tool for generating Swagger documentation with OpenApi 3.0.0 schema. This plugin automatically 
builds your Swagger UI (v3.25) from your existing cake models and routes. A redoc option is also available.

- Creates paths from your [RESTful](https://book.cakephp.org/4/en/development/rest.html) routes.
- Creates schema from your Entities and Tables.
- Provides additional functionality through Annotations and Doc Blocks.

[Demo Site](http://cakephpswaggerbake.cnizz.com/) | 
[Demo Code](https://github.com/cnizzardini/cakephp-swagger-bake-demo) | 
[Screenshot](assets/screenshot.png) |
[Console Demo](assets/console-demo.svg) 

## Installation

SwaggerBake requires CakePHP4 and a few dependencies that will be automatically installed via composer.

```
composer require cnizzardini/cakephp-swagger-bake
```

Add the plugin to `Application.php`:

```php
$this->addPlugin('SwaggerBake');
```

## Basic Usage

Get going in just four easy steps:

- Create a base swagger.yml file in `config\swagger.yml`. An example file is provided [here](assets/swagger.yml). 

- Create a `config/swagger_bake.php` file. See the example file [here](assets/swagger_bake.php) for further 
explanation.

- Create a route for the SwaggerUI page in `config/routes.php`. See Extensibility for other ways to diplay Swagger.

```php
$builder->connect('/', ['controller' => 'Swagger', 'action' => 'index', 'plugin' => 'SwaggerBake']);
```

- Use the `swagger bake` command to generate your swagger documentation. 

```sh
bin/cake swagger bake
```

Using the above example you should now see your swagger documentation after browsing to http://your-project/api

### Hot Reload Swagger JSON

You can enable hot reloading. This setting re-generates swagger.json on each reload of Swagger UI. Simply set 
`hotReload` equal to `true` in your `config/swagger_bake.php` file. This is not recommended for production.

## Annotations and Doc Block

SwaggerBake will parse some of your doc blocks for information. The first line reads as the Path Summary and the 
second as the Path Description, `@see` and `@deprecated` are also supported.

```php
/**
 * Path Summary
 * 
 * This is the path description
 * @see https://book.cakephp.org/4/en/index.html The link and this description appear in Swagger
 * @deprecated
 */
public function index() {}
```

SwaggerBake provides some optional Annotations for enhanced functionality.

#### `@SwagPaginator`
Method level annotation for adding  [CakePHP Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html) 
query parameters. This will add the following query params to Swagger:
- page
- limit
- sort
- direction

```php
use SwaggerBake\Lib\Annotation\SwagPaginator;

/**
 * @SwagPaginator
 */
public function index() {
    $employees = $this->paginate($this->Employees);
    $this->set(compact('employees'));
    $this->viewBuilder()->setOption('serialize', ['employees']);
}
```

#### `@SwagQuery`
Method level annotation for adding query parameters.

```php
use SwaggerBake\Lib\Annotation\SwagQuery;

/**
 * @SwagQuery(name="queryParamName", type="string", required=false)
 */
public function index() {}
```

#### `@SwagForm`
Method level annotation for adding form data fields.

```php
use SwaggerBake\Lib\Annotation\SwagForm;

/**
 * @SwagForm(name="fieldName", type="string", required=false)
 */
public function index() {}
```

#### `@SwagHeader`
Method level annotation for adding header parameters.

```php
use SwaggerBake\Lib\Annotation\SwagHeader;

/**
 * @SwagHeader(name="X-HEAD-ATTRIBUTE", type="string", required=false)
 */
public function index() {}
```

#### `@SwagSecurity`
Method level annotation for adding authentication requirements.

```php
use SwaggerBake\Lib\Annotation\SwagSecurity;

/**
 * @SwagSecurity(name="BearerAuth", scopes="")
 */
public function index() {}
```

#### `@SwagResponseSchema`
Method level annotation for defining custom response schema. Leave refEntity empty to define no schema.

```php
use SwaggerBake\Lib\Annotation\SwagResponseSchema;

/**
 * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Lead", description="summary", httpCode=200)
 * @Swag\SwagResponseSchema(refEntity="", description="fatal error", httpCode=500)
 */
public function index() {}
```

#### `@SwagPath`
Class level annotation for exposing controllers to Swagger UI. You can hide entire paths (controllers) with this 
annotation.

```php
use SwaggerBake\Lib\Annotation\SwagPath;

/**
 * @SwagPath(isVisible=false)
 */
class UsersController extends AppController {
```

#### `@SwagEntity`
Class level annotation for exposing entities to Swagger UI.  You can hide entities with this annotation.

```php
use SwaggerBake\Lib\Annotation\SwagEntity;

/**
 * @SwagEntity(isVisible=true)
 */
class Employee extends Entity {
```

#### `@SwagEntityAttribute`
Class level annotation for customizing Schema Attributes with @SwagEntityAttribute

```php
use SwaggerBake\Lib\Annotation\SwagEntityAttribute;

/**
 * @SwagEntityAttribute(name="modified", type="string", readOnly=true, required=false)
 */
class Employee extends Entity {
```

### Extending SwaggerBake

There are several options to extend functionality.

#### Using Your Own SwaggerUI

You may use your own swagger install in lieu of the version that comes with SwaggerBake. Simply don't add a custom 
route as indicated in step 3 of Basic Usage. In this case just reference the generated swagger.json with your own 
Swagger UI install.

#### Using Your Own Controller

You might want to perform some additional logic (checking for authentication) before rendering the built-in Swagger UI. 
This is easy to do. Just create your own route and controller, then reference the built-in layout and template: 

```php
// config/routes.php
$builder->connect('/my-swagger-docs', ['controller' => 'MySwagger', 'action' => 'index']);
```

Use beforeFilter() and index() methods from [SwaggerController](src/Controller/SwaggerController.php)

#### Generate Swagger On Your Terms

There a three options for generating swagger.json:

1. Call `swagger bake` which can be included as part of your build process.

2. Enable the `hotReload` option in config/swagger_bake.php (recommended for local development only).

3. Call SwaggerBake programmatically: 

```php
$swagger = (new \SwaggerBake\Lib\Factory\SwaggerFactory())->create();
$swagger->toArray(); # returns swagger array
$swagger->toString(); # returns swagger json
$swagger->writeFile('/full/path/to/your/swagger.json'); # writes swagger.json
```

## Console Commands

In addition to `swagger bake` these console helpers provide insight into how your Swagger documentation is generated.

#### `swagger routes` 
Displays a list of routes that can be viewed in Swagger.

```sh
bin/cake swagger routes
```

#### `swagger models` 
Displays a list of models that can be viewed in Swagger.

```sh
bin/cake swagger models
```

## Details

- Swagger uses your existing swagger.yml as a base for adding additional paths and schema.
- Generates JSON based on the OpenAPI 3 specification. I am still working on implementing the full spec.
- All Schemas and Paths generated must have the following in your CakePHP Application:
  - App\Model\Entity class (for schemas only)
  - App\Controller class
  - Must be a valid route
  - Entity attributes must not be marked as hidden to be included (for schemas only)
- SwaggerBake has been developed for application/json and has not been tested with application/xml.

## Supported Versions

This is built for CakePHP 4.x only.

| Version | Cake Version  | Supported | Unit Tests | Notes | 
| ------------- | ------------- | ------------- | ------------- | ------------- | 
| 2.* | 4.0 | Yes  | Yes | Currently supported | 
| 1.* | 3.8 | No  | No | 1.* is being left available for possible Cake 3 support in the future | 

## Common Issues

### Swagger UI 

`No API definition provided.`

Verify that swagger.json exists.

### SwaggerBakeRunTimeExceptions 

`Unable to create swagger file. Try creating an empty file first or checking permissions`

Create the swagger.json manually matching the path in your `config/swagger_bake.php` file.

`Output file is not writable`

Change permissions on your `swagger.json file`, `764` should do.

`Controller not found`

Make sure a controller actually exists for the route resource. 

### Other Issues

#### Missing actions (missing paths) in Swagger

By default Cake RESTful resources will only create routes for index, view, add, edit and delete. You can add and remove 
paths using CakePHPs route resource functionality. Read the 
[Cake Routing documentation](https://book.cakephp.org/4/en/development/routing.html) which describes in detail how to 
add, remove, modify, and alter routes. 

## Reporting Issues

This is a new library so please take some steps before reporting issues. You can copy & paste the JSON SwaggerBake 
outputs into https://editor.swagger.io/ which will automatically convert the JSON into YML and display potential 
schema issues.

Please included the following in your issues a long with a brief description:

- Steps to Reproduce
- Actual Outcome
- Expected Outcome

Feature requests are welcomed.

## Contribute

Send pull requests to help improve this library. You can include SwaggerBake in your primary Cake project as a 
local source to make developing easier:

- Make a clone of this repository

- Remove `cnizzardini\cakephp-swagger-bake` from your `composer.json`

- Add a paths repository to your `composer.json`
```
"minimum-stability": "dev",
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

## Unit Tests

```sh
vendor/bin/phpunit
```