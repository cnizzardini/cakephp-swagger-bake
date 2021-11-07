# Swagger Bake
#### A delightfully tasty plugin for generating OpenAPI, Swagger and Redoc

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cnizzardini/cakephp-swagger-bake.svg?style=flat-square)](https://packagist.org/packages/cnizzardini/cakephp-swagger-bake)
[![Build](https://github.com/cnizzardini/cakephp-swagger-bake/workflows/Build/badge.svg?branch=master)](https://github.com/cnizzardini/cakephp-swagger-bake/actions)
[![Coverage Status](https://coveralls.io/repos/github/cnizzardini/cakephp-swagger-bake/badge.svg?branch=master)](https://coveralls.io/github/cnizzardini/cakephp-swagger-bake?branch=master)
[![MixerApi](https://img.shields.io/badge/mixer-api-red?logo=data:image/png;base64,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)](http://mixerapi.com)
[![CakePHP](https://img.shields.io/badge/cakephp-^4.2-red?logo=cakephp)](https://book.cakephp.org/4/en/index.html)
[![Minimum PHP Version](https://img.shields.io/badge/php-^8.0-8892BF.svg?logo=php)](https://php.net/)
[![OpenAPI](https://img.shields.io/badge/openapi-3.0-green?logo=openapi-initiative)](https://www.openapis.org/)

Automatically generate OpenApi, Swagger, and Redoc documentation from your existing CakePHP code

- Creates OpenApi paths and operations from your [RESTful](https://book.cakephp.org/4/en/development/rest.html) routes 
and controllers.
- Creates OpenAPI Schema from your Entities and Tables.
- Integrates with: 
[Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html), 
[friendsofcake/search](https://github.com/FriendsOfCake/search), 
[Authentication](https://book.cakephp.org/authentication/2/en/index.html), 
[Validator](https://api.cakephp.org/4.0/class-Cake.Validation.Validator.html), and 
[Bake](#bake-theme).
- Provides additional functionality through Attributes and Doc Blocks.

[Swagger Bake Demo](http://cakephpswaggerbake.cnizz.com/) | [Demo Code](https://github.com/cnizzardini/cakephp-swagger-bake-demo) | 
[Swagger/MixerAPI Demo](https://demo.mixerapi.com/) | [Demo Code](https://github.com/mixerapi/demo)

This is built for CakePHP 4.x only. Supported versions:

| Version | Branch | Cake Version  | PHP Version | 
| ------------- | ------------- | ------------- | ------------- |
| 2.* | master | 4.2 - 4.3 | 8.0+  |
| 1.* | 1.x | 4.0 - 4.3 | 7.2+  | 

## Table of Contents
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Attributes](#attributes)
- [Events](#event-system)
- [Customizing Exception Responses](#customizing-exception-responses)
- [Extending Views and Controllers](#extending-views-and-controllers)
- [Multiple Instances of SwaggerBake](#multiple-instances-of-swagger-bake)
- [Debug Commands](#debug-commands)
- [Bake Theme](#bake-theme)
- [Common Issues](#common-issues)
- [Contributing](#contribute)

## Installation

SwaggerBake requires CakePHP4 and a few dependencies that will be automatically installed via composer.

```console
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

For standard applications that have not split their API into plugins, the automated setup should work. Otherwise 
use the manual setup. 

### Automated Setup

Run the install command and then [add a route](#add-route).

```console
bin/cake swagger install
```

### Manual Setup

- Create a base [swagger.yml](assets/swagger.yml) file at `config\swagger.yml`. An example file is provided 
[here](assets/swagger.yml). 

- Create a [swagger_bake.php](assets/swagger_bake.php) config file at `config/swagger_bake.php` file. See the example 
file [here](assets/swagger_bake.php) for further explanation. Then just add a route.

### Add Route

Create a route for the SwaggerUI page in `config/routes.php`, example:

```php
$builder->connect(
    '/api',
    ['plugin' => 'SwaggerBake', 'controller' => 'Swagger', 'action' => 'index']
);
``` 

You can now browse to either `/api` for swagger or `/api?doctype=redoc` for redoc. Your OpenAPI JSON
will exist at `/api/swagger.json`.

## Getting Started

- You can generate OpenAPI json from the command line at anytime by running: 

```console
bin/cake swagger bake
```

- If Hot Reload is enabled ([see config](assets/swagger_bake.php)) OpenAPI will be generated each time you browse 
to SwaggerUI (or Redoc) in your web browser.

- You can also generate OpenAPI programmatically: 

```php
$swagger = (new \SwaggerBake\Lib\SwaggerFactory())->create();
$swagger->getArray(); # returns swagger array
$swagger->toString(); # returns swagger json
$swagger->writeFile('/full/path/to/your/swagger.json'); # writes swagger.json
```

- Checkout the [debug commands](#debug-commands) for troubleshooting and the [bake theme](#bake-theme) for generating 
RESTful controllers.

### Routes

Your [RESTful routes](https://book.cakephp.org/4/en/development/routing.html#restful-routing) are used to build 
OpenAPI paths and operations.

### Controllers

SwaggerBake will parse the [DocBlocks](https://docs.phpdoc.org/latest/guides/docblocks.html) on your controller 
actions for additional OpenAPI data.

```php
/**
 * OpenAPI Operation Summary
 * 
 * This displays as the operations long description
 * 
 * @see https://book.cakephp.org/4/en/index.html The link and this description appear in OpenAPI
 * @deprecated Indicates the operation is deprecated
 * @throws \Cake\Http\Exception\BadRequestException An optional description for the HTTP 400
 * @throws \Exception An optional description for the HTTP 500
 */
public function index() {}
```

If you prefer, you may use the [OpenApiOperation](docs/attributes.md#OpenApiOperation), 
[OpenApiResponse](docs/attributes.md#OpenApiResponse) attributes instead. These attributes take precedence over doc 
block parsing. Read below for a full list of attributes.

### Models

OpenAPI schema is built from your Table and Entity classes and any validators you've defined in them. You may adjust
the default schema using the [OpenApiSchema](docs/attributes.md#OpenApiSchema) and
[OpenApiSchemaProperty](docs/attributes.md#OpenApiSchemaProperty) attributes.

## Attributes

For additional functionality the following [PHP8 Attributes](https://www.php.net/manual/en/language.attributes.overview.php) 
may be used. These can be imported individually from the 
`SwaggerBake\Lib\Attribute` namespace. [Read the Attributes docs](docs/attributes.md) for detailed examples.

| Attribute | Usage | Description | 
| ------------- | ------------- | ------------- |
| [OpenApiDto](docs/attributes.md#OpenApiDto) | Controller Action | Builds OpenAPI query params and request bodies from Data Transfer Objects |
| [OpenApiDtoQuery](docs/attributes.md#OpenApiDtoQuery) | DTO class property |  Builds OpenAPI query param from Data Transfer Objects |
| [OpenApiDtoRequestBody](docs/attributes.md#OpenApiDtoRequestBody) | DTO class property | Builds OpenAPI request body property from Data Transfer Objects |
| [OpenApiForm](docs/attributes.md#OpenApiForm) | Controller Action | Builds OpenAPI for application/x-www-form-urlencoded request bodies |
| [OpenApiHeader](docs/attributes.md#OpenApiHeader) | Controller Action | Create OpenAPI header parameters |
| [OpenApiOperation](docs/attributes.md#OpenApiOperation) | Controller Action | Modifies OpenAPI operation |
| [OpenApiPaginator](docs/attributes.md#OpenApiPaginator) | Controller Action | Create OpenAPI query params from CakePHP Paginator Component |
| [OpenApiPath](docs/attributes.md#OpenApiPath) | Controller | Modifies OpenAPI paths |
| [OpenApiPathParam](docs/attributes.md#OpenApiPathParam) | Controller Action | Modify an existing OpenAPI path parameter |
| [OpenApiQueryParam](docs/attributes.md#OpenApiQueryParam) | Controller Action | Builds OpenAPI query param |
| [OpenApiRequestBody](docs/attributes.md#OpenApiRequestBody) | Controller Action | Modify OpenAPI request body |
| [OpenApiResponse](docs/attributes.md#OpenApiResponse) | Controller Action | Modify OpenAPI response |
| [OpenApiSchema](docs/attributes.md#OpenApiSchema) | Entity | Modifies OpenAPI schema |
| [OpenApiSchemaProperty](docs/attributes.md#OpenApiSchemaProperty) | Entity | Modifies an OpenAPI schema property |
| [OpenApiSearch](docs/attributes.md#OpenApiSearch) | Controller Action | Create OpenAPI query params from CakePHP Search plugin |
| [OpenApiSecurity](docs/attributes.md#OpenApiSecurity) | Controller Action | Create/modify OpenAPI security |

## Event System

SwaggerBake comes with an [event system](docs/events.md) to allow for further control over your OpenAPI schema.

| Event | Description | 
| ------------- | ------------- |
| [SwaggerBake.Operation.created](docs/events.md#operation-created) | Dispatched each time an OpenAPI Path > Operation is created |
| [SwaggerBake.Path.created](docs/events.md#path-created) | Dispatched each time an OpenAPI Path is created |
| [SwaggerBake.Schema.created](docs/events.md#schema-created) | Dispatched each time an OpenAPI Schema is created |
| [SwaggerBake.initialize](docs/events.md#initialize) | Dispatched during initialization phase on SwaggerBake |
| [SwaggerBake.beforeRender](docs/events.md#before-render) | Dispatched before SwaggerBake outputs OpenAPI JSON |

## Customizing Exception Response Samples

By default, SwaggerBake uses `'#/components/schemas/Exception'` as your OpenAPI documentations Exception schema. See the 
default [swagger.yml](assets/swagger.yml) and `exceptionSchema` in [swagger_bake.php](assets/swagger_bake.php) for more 
info. You can further customize with attributes and `@throws`.

### OpenApiResponse

Using the [OpenApiResponse](docs/attributes.md#OpenApiResponse) attribute:

```php
#[OpenApiResponse(statusCode: '422', ref: '#/components/schemas/BadRequest')]
```

### `@throws` tag

You can provide custom schemas for exceptions by adding schema to your YAML at 
`#/x-swagger-bake/components/schemas/app-exceptions`, referencing the FQN of the exception with `x-exception-fqn`, and 
then adding the exception to your doc block:

```php
/**
 * @throws \MixerApi\ExceptionRender\ValidationException
 */
```

OpenAPI YAML:

```yaml
x-swagger-bake:
  components:
    schemas:
      app-exceptions:
        ValidationException:
          x-exception-fqn: '\MixerApi\ExceptionRender\ValidationException'
          type: object
          properties:
            exception:
              type: string
              example: ValidationException
            message:
              type: string
              example: Error saving resource `Schema`
            url:
              type: string
              example: /url/path
            code:
              type: integer
              example: 422
            violations:
              type: array
              items:
                $ref: '#/x-swagger-bake/components/schemas/app-exceptions/Violation'
```

## Extending Views and Controllers

It's possible to write extensions for SwaggerBake. Read the [extensions documentation](docs/extensions.md). There are 
several other options to extend functionality documented below:

#### Using Your Own SwaggerUI

You may use your own swagger or redoc install in lieu of the version that comes with SwaggerBake. Simply don't add a custom 
route as indicated in the installation steps. In this case just reference the generated swagger.json within your 
userland Swagger UI install.

#### Using Your Own Controller

You might want to perform some additional logic (checking for authentication) before rendering the built-in Swagger UI. 
This is easy to do. Just create your own route and controller, then reference the built-in layout and template: 

```php
// config/routes.php
$builder->connect('/my-swagger-docs', ['controller' => 'MySwagger', 'action' => 'index']);
```

To get started, copy [SwaggerController](src/Controller/SwaggerController.php) into your project.

#### Using Your Own Layout and Templates

You will need to use your own controller (see above). From there you can copy the [layouts](templates/layout) and 
[templates](templates/Swagger) into your project and inform your controller action to use them instead. Checkout out 
the CakePHP documentation on [Views](https://book.cakephp.org/4/en/views.html) for specifics. This can be useful if 
you'd like to add additional functionality to SwaggerUI (or Redoc) using their APIs or if your project is not 
installed in your web servers document root (i.e. a sub-folder).

## Multiple Instances of Swagger Bake

If your application has multiple APIs that are split into plugins you can generate unique OpenAPI schema, Swagger UI, 
and Redoc for each plugin. Setup a new `swagger_bake.php` and `swagger.yaml` in `plugins/OtherApi/config`. These 
configurations should point to your plugins paths and namespaces. Next, create a custom 
[SwaggerController](src/Controller/SwaggerController.php) and load the configuration within `initialize()`:

```php
    public function initialize(): void
    {
        parent::initialize();
        Configure::load('OtherApi.swagger_bake', 'default', false); // note: `false` for the third argument is important
        $this->loadComponent('SwaggerBake.SwaggerUi');
    }
```

When running `bin/cake swagger bake` you will need to specify your plugins swagger_bake config:

```console
bin/cake swagger bake --config OtherApi.swagger_bake
```

## Debug Commands

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

### Missing routes

Make sure yours route are properly defined in `config/routes.php` per the 
[CakePHP RESTful routing](https://book.cakephp.org/4/en/development/routing.html#restful-routing) documentation.

### Missing request or response samples

Sample schema is determined using [CakePHP naming conventions](https://book.cakephp.org/4/en/intro/conventions.html). 
Does your controller name match your model names? For customizing response schema see 
[OpenApiResponse](docs/attributes.md#OpenApiResponse).

### Missing request schema

Sample schema is determined using [CakePHP naming conventions](https://book.cakephp.org/4/en/intro/conventions.html).
Does your controller name match your model names? For customizing request schema see 
[OpenApiRequestBody](docs/attributes.md#OpenApiRequestBody).

### Missing CSRF token body

Either disable CSRF protection on your main route in `config/routes.php` or enable CSRF protection in Swagger 
UI. The library does not currently support adding this in for you.

### HTTP DELETE issues with Swagger UI

Swagger UI sends HTTP DELETE without an `accept` header. If the record does not exist, an exception is generated. 
This results in an HTML response being generated which can be quite large and cause the UI to be slow to render. To 
get around this you can force an `accept` value on the header using the CakePHP middleware:

```php
# src/Application.php

public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
	$middlewareQueue
	    ->add(function(ServerRequestInterface $request, RequestHandlerInterface $handler){
	        $accept = $request->getHeader('accept');
	        if ($request->getMethod() === 'DELETE' && reset($accept) === '*/*') {
	            $request = $request->withHeader('accept', 'application/json');
	        }

	        return $handler->handle($request);
	    });

	// other middleware...
	
	return $middlewareQueue;
}
```

Read more about [CakePHP middleware](https://book.cakephp.org/4/en/controllers/middleware.html) in the official 
documentation.

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

Check out the [extensions](docs/extensions.md) documentation to add functionality to this project.

### Tests + Analysis

PHPUnit Test Suite:

```console
composer test
```

PHPUnit, PHPCS, PHPSTAN, and PHPMD:

```console
composer analyze
```

[GrumPHP](https://github.com/phpro/grumphp) can be used to run tests and static analyzers in a pre-commit hook.

```console
composer grumphp-init
```

I've set grumphp to be installed globally: https://github.com/phpro/grumphp/blob/master/doc/installation/global.md