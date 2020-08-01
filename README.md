# SwaggerBake plugin for CakePHP4

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cnizzardini/cakephp-swagger-bake.svg?style=flat-square)](https://packagist.org/packages/cnizzardini/cakephp-swagger-bake)
[![Build Status](https://travis-ci.org/cnizzardini/cakephp-swagger-bake.svg?branch=master)](https://travis-ci.org/cnizzardini/cakephp-swagger-bake)
[![Coverage Status](https://coveralls.io/repos/github/cnizzardini/cakephp-swagger-bake/badge.svg?branch=master)](https://coveralls.io/github/cnizzardini/cakephp-swagger-bake?branch=master)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

A delightfully tasty tool for generating Swagger documentation with OpenApi 3.0.0 schema. This plugin automatically 
builds your Swagger UI and ReDoc from your existing cake models and routes.

- Creates OpenApi paths and operations from your [RESTful](https://book.cakephp.org/4/en/development/rest.html) routes 
and controllers.
- Creates OpenAPI Schema from your Entities and Tables.
- Integrates with: 
[Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html), 
[friendsofcake/search](https://github.com/FriendsOfCake/search), 
[Authentication](https://book.cakephp.org/authentication/2/en/index.html), 
[Validator](https://api.cakephp.org/4.0/class-Cake.Validation.Validator.html), and 
[Bake](#bake-theme).
- Provides additional functionality through Annotations and Doc Blocks.


[Demo Site](http://cakephpswaggerbake.cnizz.com/) | 
[Demo Code](https://github.com/cnizzardini/cakephp-swagger-bake-demo) | 
[Screenshot](assets/screenshot.png) |
[Console Demo](assets/console-demo.svg) 

## Table of Contents
- [Installation](#installation)
- [Setup](#setup)
- [Automatic Documentation](#automatic-documentation)
- [Doc Blocks](#doc-blocks)
- [Annotations for Extended Functionality](#annotations-for-extended-functionality)
- [Extending SwaggerBake](#extending-swaggerbake)
- [Console Commands](#console-commands)
- [Bake Theme](#bake-theme)
- [...](#details)

## Installation

SwaggerBake requires CakePHP4 and a few dependencies that will be automatically installed via composer.

```
composer require cnizzardini/cakephp-swagger-bake
```

Run `bin/cake plugin load SwaggerBake` or manually load the plugin: 

```php
# src/Application.php
public function bootstrap(): void
{
    // other logic...
    $this->addPlugin('SwaggerBake');
}
```

## Setup

For standard applications that have not split their API into plugins, the automated setup should work. Otherwise 
use the manual setup.

### Automated Setup

Run `bin/cake swagger install`

Then just [add a route](#last-step).

### Manual Setup

- Create a base swagger.yml file in `config\swagger.yml`. An example file is provided [here](assets/swagger.yml). 

- Create a `config/swagger_bake.php` file. See the example file [here](assets/swagger_bake.php) for further 
explanation.

- Create a route for the SwaggerUI page in `config/routes.php`. 

### Last Step

Create a route for the SwaggerUI page in `config/routes.php`, example:

```php
$builder->connect('/my-swagger-ui', ['controller' => 'Swagger', 'action' => 'index', 'plugin' => 'SwaggerBake']);
``` 

If Hot Reload is enabled ([see config](assets/swagger_bake.php)) then you should be able to browse to the above 
route. Otherwise you must first run `bin/cake swagger bake` to generate your swagger documentation. 
 
## Automatic Documentation

I built this library to reduce the need for annotations to build documentation. SwaggerBake will automatically 
build the following from your existing routes and models without additional effort:

- Paths
    - Resource (route)
- Operations
    - Summary and description
    - GET, POST, PATCH, DELETE
    - Form fields and JSON using your Cake models
    - Responses
    - Sub resources
    - Security/Authentication
- Schema

[See details](#details) for how CakePHP conventions are interpreted into OpenAPI 3.0 schema.

SwaggerBake works with your existing YML definitions and will not overwrite anything. By default, it uses 
components > schemas > Exception as your Swagger documentations Exception schema. See the default 
[swagger.yml](assets/swagger.yml) and `exceptionSchema` in [swagger_bake.php](assets/swagger_bake.php) for more info.

## Doc Blocks

SwaggerBake will parse your [DocBlocks](https://docs.phpdoc.org/latest/guides/docblocks.html) for information. The 
first line reads as the Operation Summary and the second as the Operation Description, `@see`, `@deprecated`, and 
`@throws` are also supported. Throw tags use the Exception classes HTTP status code. For instance, a 
`MethodNotAllowedException` displays as a 405 response in Swagger UI, while a standard PHP Exception displays as a 500 
code.

```php
/**
 * Swagger Operation Summary
 * 
 * This displays as the operations long description
 * 
 * @see https://book.cakephp.org/4/en/index.html The link and this description appear in Swagger
 * @deprecated
 * @throws BadRequestException An optional bad request description here
 * @throws Exception
 */
public function index() {}
```

## Annotations for Extended Functionality

SwaggerBake provides some optional Annotations for enhanced functionality. These can be imported individually from 
`SwaggerBake\Lib\Annotation` or set to an alias such as `Swag`: `use SwaggerBake\Lib\Annotation as Swag`.

[Read the Annotations README](src/Lib/Annotation#swaggerbake-annotations) for detailed examples.

#### `@SwagPaginator`
Method level annotation for adding [CakePHP Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html) 
query parameters: page, limit, sort, and direction. [Read more](src/Lib/Annotation#swagpaginator)

```php
/**
 * @Swag\SwagPaginator
 */
public function index() {}
```

#### `@SwagSearch`
Method level annotation for documenting search parameters using the popular [friendsofcake/search](https://github.com/FriendsOfCake/search) plugin.
 
[Read more](src/Lib/Annotation#swagsearch)

```php
use SwaggerBake\Lib\Extension\CakeSearch\Annotation\SwagSearch;
/**
 * @SwagSearch(tableClass="\App\Model\Table\ActorsTable", collection="default")
 */
public function index() {}
```

#### `@SwagQuery` 
Method level annotation for adding query parameters. 

[Read more](src/Lib/Annotation#swagquery)

```php
/**
 * @Swag\SwagQuery(name="queryParamName", type="string", description="string")
 */
public function index() {}
```

#### `@SwagForm`
Method level annotation for adding form data fields. 

[Read more](src/Lib/Annotation#swagform)

```php
/**
 * @Swag\SwagForm(name="fieldName", type="string", description="string", required=false, enum={"a","b"})
 */
public function index() {}
```

#### `@SwagDto`
Method level annotation for building query or form parameters from a DataTransferObject. DTOs are more than just a 
best practice. Using them with SwaggerBake greatly reduces the amount of annotations you need to write. Consider 
using a DTO in place of SwagQuery or SwagForm. 

[Read more](src/Lib/Annotation#swagdto)

##### `@SwagDtoQuery`
Property level annotation for use in your SwagDto classes. 

[Read more](src/Lib/Annotation#swagdtoquery)

##### `@SwagDtoForm`
Property level annotation for use in your SwagDto classes. 

[Read more](src/Lib/Annotation#swagdtoform)

#### `@SwagHeader`
Method level annotation for adding header parameters. 

[Read more](src/Lib/Annotation#swagheader)

```php
/**
 * @Swag\SwagHeader(name="X-HEAD-ATTRIBUTE", type="string", description="string")
 */
public function index() {}
```

#### `@SwagPathParameter`
Method level annotation for modifying path parameters. 

[Read more](src/Lib/Annotation#swagpathparameter)

```php
/**
 * @Swag\SwagPathParameter(name="id", type="integer", format="int64", description="ID")
 */
public function view($id) {}
```

#### `@SwagSecurity`
Method level annotation for adding authentication requirements. This annotation takes precedence over settings that 
SwaggerBake gathers from AuthenticationComponent. 

[Read details below](#details)

```php
/**
 * @Swag\SwagSecurity(name="BearerAuth", scopes={"Read","Write"})
 */
public function index() {}
```

#### `@SwagOperation`
Method level annotation for OpenApi Operations. 

[Read more](src/Lib/Annotation#swagoperation)

```php
/**
 * @Swag\SwagOperation(isVisible=false, tagNames={"MyTag","AnotherTag"}, showPut=false)
 */
public function index() {}
```

#### `@SwagRequestBody`
Method level annotation for describing request body. Set ignoreCakeSchema for full control over request body. 

[Read more](src/Lib/Annotation#swagrequestbody)

```php
/**
 * @Swag\SwagRequestBody(description="my description", required=true, ignoreCakeSchema=true)
 */
public function index() {}
```

#### `@SwagRequestBodyContent`
Method level annotation for describing custom content in request body. The mimeTypes parameter is optional. If empty, 
all mimeTypes defined as `requestAccepts` in your swagger_bake.php will be used. 

[Read more](src/Lib/Annotation#swagrequestbodycontent)

- `mimeType` has been deprecated in >= v1.5, use array form with `mimeTypes`

```php
/**
 * @Swag\SwagRequestBodyContent(refEntity="#/components/schemas/Actor", mimeTypes={"application/json"})
 */
public function index() {}
```

#### `@SwagResponseSchema`
Method level annotation for defining response schema. 

[Read more](src/Lib/Annotation#swagresponseschema)

- `mimeType` is deprecated in >= v1.5, use `mimeTypes` as an array.
- `httpCode` is deprecated in >= v1.3, use `statusCode` 

```php
/**
 * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Actor", description="Summary", statusCode="200")
 */
public function index() {}
```

#### `@SwagPath`
Class level annotation for exposing controllers to Swagger UI. You can hide entire controllers with this annotation. 

[Read more](src/Lib/Annotation#swagpath)

```php
/**
 * @Swag\SwagPath(isVisible=false, description="optional description", summary="operational summary")
 */
class UsersController extends AppController {
```

#### `@SwagEntity`
Class level annotation for exposing entities to Swagger UI. By default, all entities with routes will display as Swagger 
schema. You can hide a schema or display a schema that does not have an associated route. 

[Read more](src/Lib/Annotation#swagentity)

```php
/**
 * @Swag\SwagEntity(isVisible=false, title="optional title", description="optional description")
 */
class Employee extends Entity {
```

#### `@SwagEntityAttribute`
Class level annotation for customizing Schema Attributes. 

[Read more](src/Lib/Annotation#swagentityattribute)

```php
/**
 * @Swag\SwagEntityAttribute(name="modified", type="string", description="string")
 */
class Employee extends Entity {
```

## Extending SwaggerBake

There are several options to extend functionality.

#### Using Your Own SwaggerUI

You may use your own swagger install in lieu of the version that comes with SwaggerBake. Simply don't add a custom 
route as indicated in the installation steps. In this case just reference the generated swagger.json within your 
userland Swagger UI install.

#### Using Your Own Controller

You might want to perform some additional logic (checking for authentication) before rendering the built-in Swagger UI. 
This is easy to do. Just create your own route and controller, then reference the built-in layout and template: 

```php
// config/routes.php
$builder->connect('/my-swagger-docs', ['controller' => 'MySwagger', 'action' => 'index']);
```

Use [SwaggerController](src/Controller/SwaggerController.php) as your base.

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

## Bake Theme

SwaggerBake comes with [Bake templates](templates/bake) for scaffolding RESTful controllers compatible with SwaggerBake 
and OpenAPI 3.0 schema. Using the bake theme is completely optional, but will save you some time since the default 
bake theme is not specifically designed for RESTful APIs.

```
bin/cake bake controller {Name} --theme SwaggerBake
```

## Details

[OpenAPI Schema Support Roadmap](https://docs.google.com/spreadsheets/d/e/2PACX-1vRTWE7nsTouFdHZsG6OKlZ-1lHeJGI0wqNlRVEgiG4eCFY0dMxkBLaw313mU_a73U7emoRdFcGPUq94/pubhtml)

- Swagger uses your existing swagger.yml as a base for adding additional paths and schema.
- Generates JSON based on the OpenAPI 3 specification. I am still working on implementing the full spec.
- All Schemas and Paths generated must have the following in your CakePHP Application:
  - App\Model\Entity class (for schemas only)
  - App\Controller class
  - Must be a valid route
- Entity Attributes: 
  - Hidden attributes will not be visible
  - Primary Keys will be set to read only by default.
  - DateTime fields named `created` and `modified` are automatically set to read only per Cake convention.
- CRUD Responses
  - Index, Edit, Add, and View methods default to an HTTP 200 with the Controllers related Cake Entity schema.
  - Delete defaults to HTTP 204 (no content). 
- Table Validators:
  - Reads in [Validator](https://api.cakephp.org/4.0/class-Cake.Validation.Validator.html) rules such as 
  requirePresence, minLength, maxLength, basic math comparison operators, regex, inList, hasAtLeast, and hasAtMost.
- Security Scheme 
  - Leverages the [CakePHP AuthenticationComponent](https://book.cakephp.org/authentication/2/en/index.html)
  - Will automatically set security on operations if a single [securityScheme](https://swagger.io/docs/specification/authentication/) 
  is defined in your swagger.yaml. If more than one security schema exists you will need to use `@SwagSecurity`.
  - `@SwagSecurity` takes precedence.
- SwaggerBake has been developed primarily for application/json and application/x-www-form-urlencoded, but does have 
some support for application/xml and *should* work with application/vnd.api+json.

SwaggerBake does not document schema associations. If your application includes associations on things like 
GET requests, you can easily add them into your swagger documentation through the OpenAPI `allOf` property. Since 
SwaggerBake works in conjunction with OpenAPI YAML you can easily add a new schema with this association. Below is an 
example of extending an existing City schema to include a Country association.

```yaml
# in your swagger.yml
components:
  schemas:
    CityExtended:
      description: 'City with extended information including Country'
      type: object
      allOf:
        - $ref: '#/components/schemas/City'
        - type: object
          properties:
            country:
              $ref: '#/components/schemas/Country'

```

Then in your controller action you'd specify the Schema: 

```php
/**
 * View method
 * @Swag\SwagResponseSchema(refEntity="#/components/schemas/CityExtended")
 */
public function view($id)
{
    $this->request->allowMethod('get');
    $city = $this->Cities->get($id, ['contain' => ['Countries']]);
    $this->set(compact('cities'));
    $this->viewBuilder()->setOption('serialize', 'cities');
}
```

The demo application includes this and many other examples of usage. Read more about `oneOf`, `anyOf`, `allOf`, and 
`not` in the [OpenAPI 3 documentation](https://swagger.io/docs/specification/data-models/oneof-anyof-allof-not/).

## Supported Versions

This is built for CakePHP 4.x only. A cake-3.8 option is available, but not supported.

| Version | Cake Version  | Supported | Unit Tests | Notes | 
| ------------- | ------------- | ------------- | ------------- | ------------- | 
| 1.* | 4.* | Yes  | Yes | Currently supported |
| cake-3.8 | 3.8.* | No  | Yes | See branch cake-3.8. Completely untested and unsupported | 

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

#### Missing CSRF token body

Either disable CSRF protection on your main route in `config/routes.php` or enable CSRF protection in Swagger 
UI. The library does not currently support adding this in for you.

#### My route isn't displaying in Swagger UI

Make sure the route is properly defined in your `config/routes.php` file.

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

- Make a fork of this repository and clone it to your localhost

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

## Coding Standards

Coding standards run as part of CI with Travis. You may run these locally with `composer check`.

## Unit Tests

```sh
vendor/bin/phpunit
```
