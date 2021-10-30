# SwaggerBake Attributes

SwaggerBake provides some optional [Attributes](https://www.php.net/manual/en/language.attributes.overview.php) for 
enhanced functionality. It is helpful to understand the [OpenApi specification](https://spec.openapis.org/oas/latest.html) 
when reading this documentation.

- `use SwaggerBake\Lib\Attribute as OpenApi;`
- `use SwaggerBake\Lib\Extension as OpenApiExt;`

## Table of Contents

| Attribute | Description | 
| ------------- | ------------- |
| [OpenApiDto](#OpenApiDto) | Builds OpenAPI query params and request bodies from Data Transfer Objects |
| [OpenApiDtoQuery](#OpenApiDtoQuery) | Builds OpenAPI query param from Data Transfer Objects |
| [OpenApiDtoRequestBody](#OpenApiDtoRequestBody) | Builds OpenAPI request body property from Data Transfer Objects |
| [OpenApiForm](#OpenApiForm) | Builds OpenAPI for application/x-www-form-urlencoded request bodies |
| [OpenApiHeader](#OpenApiHeader) | Create OpenAPI header parameters |
| [OpenApiOperation](#OpenApiOperation) | Modifies OpenAPI operation |
| [OpenApiPaginator](#OpenApiPaginator) | Create OpenAPI query params from CakePHP Paginator Component |
| [OpenApiPath](#OpenApiPath) | Modifies OpenAPI paths |
| [OpenApiPathParam](#OpenApiPathParam) | Modify an existing OpenAPI path parameter |
| [OpenApiQueryParam](#OpenApiQueryParam) | Builds OpenAPI query param |
| [OpenApiRequestBody](#OpenApiRequestBody) | Modify OpenAPI request body |
| [OpenApiResponse](#OpenApiResponse) | Modify OpenAPI response |
| [OpenApiSchema](#OpenApiSchema) | Modifies OpenAPI schema |
| [OpenApiSchemaProperty](#OpenApiSchemaProperty) | Modifies an OpenAPI schema property |
| [OpenApiSearch](#OpenApiSearch) | Create OpenAPI query params from CakePHP Search plugin |
| [OpenApiSecurity](#OpenApiSecurity) | Create/modify OpenAPI security |

### OpenApiDto

Method level attribute for building query or form parameters from a DataTransferObject. Your DTO will need to use 
the [OpenApiDtoQuery](#OpenApiDtoQuery) or [OpenApiDtoRequestBody](#OpenApiDtoRequestBody) on properties depending on 
the request type.

| Property | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| class | string | No | FQN of the DTO class. |


```php
#[OpenApiDto(class: "\App\Dto\ActorDto")]
public function index() {}
```

### OpenApiDtoQuery

Property or parameter level attribute for use in your DTO classes.

| Property | Type / Default | OA Spec | Description |
| ------------- | ------------- | ------------- | ------------- |
| name | string `""` | Y | Name of the query parameter, required if ref is not set |
| ref | string `""` | Y | An OpenApi $ref, required if name is not set |
| type | string `string` | Y | The scalar data type |
| format | string `""` | Y | A data format describing the scalar type such as `date-time`, `uuid`, or `int64` |
| description | string `""` | Y | Description of the parameter |
| example | mixed `null` | Y | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| allowReserved | bool `false` | Y | Allow reserved URI characters? |
| explode | bool `false` | Y | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| isRequired | bool `false` | Y | Is this parameter required? |
| isDeprecated | bool `false` | Y | Is this parameter deprecated? |
| allowEmptyValue | bool `false` | Y | Allow empty values? |
| enum | array `[]` | Y | An enumerated list of accepted values |
| style | string `""` | Y | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |

```php
class ActorDto {
    #[OpenApiDtoQuery(name: 'name', required: true, enum: ['A','B'])]
    private string $name;

    #[OpenApiDtoQuery(name: 'some_field', type: 'int')]
    private ?int $someField = null;
```


Via constructor property promotion:

```php
class ActorDto {
    public function __construct(
        #[OpenApiDtoQuery(name: 'name', required: true, enum: ['A','B'])]
        public string $name,
        #[OpenApiDtoQuery(name: 'some_field', type: 'int')]
        public ?int $someField = null, 
    ) {
    }
```

### OpenApiDtoRequestBody
Property or parameter level attribute for use in your DTO classes.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the schema property |
| type | string `string` | Date type such as integer, string, etc... |
| format | string `""` | Date format such as int32, date-time, etc... |
| description | string `""` | Description of the property |
| isReadOnly | bool `false` | Is the property read only? |
| isWriteOnly | bool `false` | Is the property write only? |
| isRequired | bool `false` | Is the property required? |
| multipleOf | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maximum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| isExclusiveMaximum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| minimum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| isExclusiveMinimum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| pattern | string `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| hasUniqueItems | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| enum | array `[]` | http://spec.openapis.org/oas/v3.0.3#properties |

```php
class ActorDto {
    #[OpenApiDtoRequestBody(name: 'name', isRequired: true, enum: ['A','B'])]
    private string $name;

    #[OpenApiDtoRequestBody(name: 'some_field', type: 'int')]
    private ?int $someField = null;
```

Via constructor property promotion:

```php
class ActorDto {
    public function __construct(
        #[OpenApiDtoRequestBody(name: 'name', isRequired: true, enum: ['A','B'])]
        public string $name,
        #[OpenApiDtoRequestBody(name: 'some_field', type: 'int')]
        public ?int $someField = null, 
    ) {
    }
```

### OpenApiForm
Method level attribute for adding form data fields.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the schema property |
| type | string `string` | Date type such as integer, string, etc... |
| format | string `""` | Date format such as int32, date-time, etc... |
| description | string `""` | Description of the property |
| isReadOnly | bool `false` | Is the property read only? |
| isWriteOnly | bool `false` | Is the property write only? |
| isRequired | bool `false` | Is the property required? |
| multipleOf | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maximum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| isExclusiveMaximum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| minimum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| isExclusiveMinimum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| pattern | string `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| hasUniqueItems | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| enum | array `[]` | http://spec.openapis.org/oas/v3.0.3#properties |

```php
#[OpenApiForm(name: 'one', description: 'example', isRequired: true, enum: ['A','B'])]
#[OpenApiForm(name: 'two', type: 'integer', minimum: 10, maximum: 100, multipleOf: 10)]
#[OpenApiForm(name: 'three', minLength: 8, maxLength: 64, pattern: '[a-zA-z]')]
public function add() {}
```

OpenAPI:

```yaml
      requestBody:
        description: ''
        content:
          application/x-www-form-urlencoded:
            schema:
              description: Actor Entity
              type: object
              properties:
                one:
                  type: string
                  description: example
                  required: true
                  enum:
                    - A
                    - B
                two:
                  type: integer
                  minimum: 10
                  maximum: 100
                  multipleOf: 10
                three:
                  type: string
                  minLength: 8
                  maxLength: 64
                  pattern: [a-zA-Z]
```

### OpenApiSchemaProperty
Class level attribute for customizing Schema Attributes. Note that the attribute does not have to exist in your entity.
You can add adhoc attributes as needed and optionally combine with
[Virtual Fields](https://book.cakephp.org/4/en/orm/entities.html#creating-virtual-fields).

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the schema property |
| type | string `string` | Date type such as integer, string, etc... |
| format | string `""` | Date format such as int32, date-time, etc... |
| description | string `""` | Description of the property |
| isReadOnly | bool `false` | Is the property read only? |
| isWriteOnly | bool `false` | Is the property write only? |
| isRequired | bool `false` | Is the property required? |
| multipleOf | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maximum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| isExclusiveMaximum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| minimum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| isExclusiveMinimum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| pattern | string `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| hasUniqueItems | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| enum | array `[]` | http://spec.openapis.org/oas/v3.0.3#properties |
| example | mixed `null` | http://spec.openapis.org/oas/v3.0.3#properties |

```php
#[OpenApiSchemaProperty(name: 'example_one', minLength: 5, maxLength: 10)]
#[OpenApiSchemaProperty(name: 'example_two', minLength: 5, enum: ['PG','R'], isRequired: true)]
#[OpenApiSchemaProperty(name: 'example_virtual_field', isReadOnly: true)]
class Employee extends Entity {
```

OpenAPI:

```yaml
        example_one:
          type: string
          minLength: 5
          maxLength: 10
        example_two:
          type: string
          enum:
            - PG
            - R
          required: true
        example_virtual_field:
          type: string
          readOnly: true
```

### OpenApiHeader
Method level attribute for adding [header](https://spec.openapis.org/oas/latest.html#header-object) parameters.

| Property | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| name | string | Y | Name of the query parameter |
| type | string `string` | Y |  Data type |
| description | string `""` | Y |  Description of the parameter |
| required | bool `false` | Y |  Is this parameter required? |
| allowEmpty | bool `false` | Y |  Are empty values allowed? |
| enum | array `[]` | Y |  An enumerated list of accepted values |
| deprecated | bool `false` | Y |  Is this parameter deprecated? |
| explode | bool `false` | Y |  http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| style | string `""` | Y |  http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| format | string `""` | Y |  http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| example | mixed `null` | Y |  http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |

```php
#[OpenApiHeader(name: 'X-HEAD-ATTRIBUTE', type: 'string', description: 'example')]
#[OpenApiHeader(ref: '#/x-my-project/components/parameters/my-header']
public function index() {}
```

OpenAPI:

```yaml
     parameters:
       - name: X-HEAD-ATTRIBUTE
         in: header
         description: summary
         schema:
           type: string
       - $ref: #/x-my-project/components/parameters/my-header
```

### OpenApiPaginator
Method level attribute for adding [CakePHP Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html)
query parameters: page, limit, sort, and direction. When specified with no arguments, Paginate.sortableFields will be
used to populate the options list (for `index()` actions only). User supplied options can be given using sortEnum.

| Property | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| sortEnum | array `[]` | No| A list of fields that can be sorted by. |
| useSortTextInput | boolean `false` | No | Use an input box instead of dropdown for sortable field |

```php
#[OpenApiPaginator(sortEnum: ['id', 'name'])]
public function index() {}
```

OpenAPI:

```yaml
      parameters:
        - name: page
          in: query
          schema:
            type: integer
        - name: limit
          in: query
          schema:
            type: integer
        - name: sort
          in: query
          schema:
            type: string
            enum:
              - id
              - name
        - name: direction
          in: query
          schema:
            type: string
            enum: 
              - asc
              - desc
```

### OpenApiPathParam
Method level attribute for modifying path parameters. This is for modifying existing path parameters only. Path 
parameters must first be defined in your routes file.

| Attribute | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| name | string | Yes | Name of the query parameter |
| type | string `string` | Yes | Data type |
| format | string `` | Yes | Data format |
| description | string `` | Yes | Description of the parameter |
| allowReserved | bool `false` | Yes | Allow reserved URI characters? |
| example | mixed `null` | Yes | An example value |

```php
#[OpenApiPathParam(name: 'id', type: 'integer', format: 'int64', description: 'ID')]
public function view($id) {}
```

OpenAPI: 

```yaml
        parameters:
          - name: id
            required: true
            schema:
              description: ID
              type: integer
              format: int64
```

### OpenApiOperation
Method level attribute for OpenApi Operations. 

| Property | Type / Default | OA Spec |Description | 
| ------------- | ------------- | ------------- | ------------- |
| summary | string|null `` | Yes | Operation summary, set to null to prevent reading from docblock |
| description | string|null `` | Yes | Operation description, set to null to prevent reading from docblock |
| isVisible | bool `true` | No | Setting this to false will prevent the operation from appearing in OpenApi output |
| tagNames | array `[]` | Yes | Sets tag names |
| isPut | bool `false` | No | Changes the HTTP Method to an HTTP PUT on controller `edit()` actions/methods |
| isDeprecated | bool `false` | Yes | Is the operation deprecated? |
| externalDocs | array|null `null` | Yes | External documentation |

```php
#[OpenApiOperation(
    summary: 'operation title',
    description: 'a description',
    tagNames: ['Internal API', 'External API'], 
    externalDocs: ['url' => 'http://localhost', 'description' => 'desc...']]
)]
public function index()
```

A common use-case is to hide an operation from appearing in your OpenAPI, example:

```php
#[OpenApiOperation(isVisible: false)]
public function index()
```

OpenAPI:

```yaml
  put:
    tags:
      - Custom
      - Tags
```

### OpenApiPath
Class level attribute to define scalar [Path](https://spec.openapis.org/oas/latest.html#path-item-object) values.

| Property | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| isVisible | boolean `true` | No | Is the path and its operations visible in OpenAPI  |
| ref | string `""` | Yes | An OpenAPI ref such as `#/paths/my-path` |
| description | string `""` | Yes | Overwrites the default description |
| summary | string `""` | Yes | Overwrites the default summary (if any) |

A common use-case for this is to hide a controller from appearing in your OpenApi (the default behavior). For instance,
you may have a bespoke endpoint that you don't want to publish.

```php
#[OpenApiPath(isVisible: false)]
class UsersController extends AppController
```


### OpenApiQueryParam
Method level attribute for adding query parameters.

| Property | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| name | string | Yes | Name of the query parameter |
| type | string `string` | Yes | Data type |
| ref | string | Yes | An OpenApi $ref parameter describing the query parameter |
| description | string `""` | Yes | Description of the parameter |
| required | bool `false` | Yes | Is this parameter required? |
| enum | array `[]` | Yes | An enumerated list of accepted values |
| deprecated | bool `false` | Yes | Is this parameter deprecated? |
| explode | bool `false` | Yes | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| style | string `""` | Yes | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| example | mixed `null` | Yes | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| format | string `""` | Yes | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| allowEmptyValue | bool `false` | Yes | Allow empty values? |

```php
#[OpenApiQueryParam(name: "one", required: true, description: "example description")]
#[OpenApiQueryParam(name: "two", type: "string", explode: true)]
#[OpenApiQueryParam(name: "three", enum: ["A","B","C"], deprecated: true])]
#[OpenApiQueryParam(ref: "#/x-my-project/components/parameters/my-parameter")]
public function index() {}
```

OpenAPI:

```yaml
      parameters:
        - name: one
          in: query
          description: example description
          schema:
            type: string
        - name: two
          in: query
          explode: true
          schema:
            type: string
        - name: three
          in: query
          explode: true
          deprecated: true
          schema:
            type: string
            enum:
              - A
              - B
              - C
        - $ref: #/x-my-project/components/parameters/my-parameter
```

### OpenApiRequestBody
Method level attribute for describing request body. Set ignoreCakeSchema for full control over request body.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| ref | string `""` | An optional OpenAPI $ref |
| description | string `""` | Description of the request body |
| mimeTypes | array `[]` | An array of strings of mime types to support |
| required | bool `true` | Is the request body required? |
| ignoreCakeSchema | bool `false` | Ignore cake schema |

```php
#[OpenApiRequestBody(ref: '#/components/schema/Custom', description: 'hi', ignoreCakeSchema: true, mimeTypes: ['application/json'])]
public function index() {}
```

OpenAPI:

```yaml
      requestBody:
        description: 'hi'
        content:
          application/json:
            schema:
              $ref: '#/components/schema/Custom'
```

### OpenApiResponse
Method level attribute for controller actions defining
[response object's](https://spec.openapis.org/oas/latest.html#response-object) and their schema/content.

| Property | Type / Default | OA Spec | Description |
| ------------- | ------------- | ------------- |  ------------- |
| schemaType | string `object` | Y | The schema response type, generally `"object"` or `"array"` |
| statusCode | string `200` | Y | The HTTP response code |
| mimeTypes | array `null` | Y | An array of mime types the response can, if null settings from swagger_bake config are used. |
| description | string `` | Y | Description of the response |
| ref | string `` | Y | The OpenAPI schema (e.g. `"#/components/schemas/ModelName"` |
| schemaFormat | string `` | Y | The schema format, generally only used for schemaType of string. |
| associations | array `null` | N | Adds associated tables to the response sample schema, see examples below |

Defining a multiple mimeTypes and 400-409 status code range and an expected 200 response:

```php
#[OpenApiResponse(
    statusCode: '40x',
    ref: '#/components/schemas/Exception', 
    mimeTypes: ['application/xml','application/json']
)]
#[OpenApiResponse(schemaType: 'array', ref: '#/components/schemas/Actor')]
```

OpenAPI:

```yaml
       '200':
         content:
           application/json:
             schema:
               type: array
               items:
                 $ref: '#/components/schemas/Actor'
       '40x':
         content:
           application/xml:
             schema:
               type: object
               items:
                 $ref: '#/components/schemas/Exception'
```

To include all immediately associated tables (deep nested associations are not yet supported) for an object
(resource/item) we can just set an empty array. Remember, this infers the base table from the controller.

```php
#[OpenApiResponse(associations: [])]
```

We can change the base table like so:

```php
#[OpenApiResponse(associations: ['table' => 'OtherTable'])]
```

As an array (collection) but white list specific tables (ignores all others):

```php
#[OpenApiResponse(schemaType: 'array', associations: ['whiteList' => ['Films']])]
```

### OpenApiSchema
Class level attribute for exposing entities to Swagger UI. 

| Property | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| isVisible | boolean `true` | No | All entities with routes are added to OpenAPI schema. To completely hide a schema from appearing anywhere in OpenAPI JSON output set to false |
| isPublic | boolean `true` | No | Setting to false hides the Schema from Swaggers Schema section. |
| title | string `""` | Yes | Overwrites the default title |
| description | string `""` | Yes | Overwrites the default description (if any) |

**isVisible vs isPublic:** 

`isVisible` takes precedence over `isPublic`. If you've set `isVisible` to `false` then whatever you've defined for 
`isPublic` becomes inert. If a schema is visible, but not public it be accessed via 
`#/x-swagger-bake-bake/components/schemas/EntityName`. This is helpful if you want to reduce cluter in your Swagger 
schemas, but still want the ability to reference it via `OpenApiResponseSchema`

```php
 #[OpenApiSchema(isVisble: true, isPublic: false, title: 'optional title', description: 'optional description')]
class Employee extends Entity
```

### OpenApiSearch
Method level attribute for documenting search parameters using the popular
[friendsofcake/search](https://github.com/FriendsOfCake/search) plugin.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| tableClass | string | Required FQN to the Table class |
| collection | string `default` | The Cake Search collection _(see vendor documentation)_ |

```php
 #[OpenApiSearch(tableClass: '\App\Model\Table\FilmsTable', collection: 'default')]
public function index()
{
    $this->request->allowMethod('get');
    $query = $this->Films
        ->find('search', [
            'search' => $this->request->getQueryParams(),
            'collection' => 'default'
        ])
        ->contain(['Languages']);
    $films = $this->paginate($query);

    $this->set(compact('films'));
    $this->viewBuilder()->setOption('serialize', 'films');
}
```

Friends Of Cake Search Filter:

```php
<?php
declare(strict_types=1);

namespace App\Model\Filter;

use Search\Model\Filter\FilterCollection;

class FilmsCollection extends FilterCollection
{
    public function initialize(): void
    {
        $this
            ->add('title', 'Search.Like', [
                'before' => true,
                'after' => true,
                'mode' => 'or',
                'comparison' => 'LIKE',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'fields' => ['title'],
            ])
            ->add('rating', 'Search.Value', [
                'before' => true,
                'after' => true,
                'mode' => 'or',
                'wildcardAny' => '*',
                'wildcardOne' => '?',
                'fields' => ['rating'],
            ]);
    }
```

OpenAPI:

```yaml
      parameters:
        - name: title
          in: query
          description: Like title
          schema:
            type: string
        - name: rating
          in: query
          description: Value rating
          schema:
            type: string
```

### OpenApiSecurity
Method level attribute for documenting security requirements on an Operation. This attribute takes precedence over
settings inferred from AuthenticationComponent. See the main documentation for more information. You may define
multiple.

| Property | Type / Default | OA Spec | Description | 
| ------------- | ------------- | ------------- | ------------- |
| name | string | Yes | Name of the security option (required) |
| scopes | array `[]` | Yes | Security Scopes |

Here is an example of documenting that an index endpoint requires BearerAuth security with read scope. The `name`
should match what is defined in your YAML [Security Scheme Object](https://spec.openapis.org/oas/latest.html#security-scheme-object).

```php
#[OpenApiSecurity(name: 'BearerAuth', scopes: ['read'])]
#[OpenApiSecurity(name: 'ApiKey')]
public function index()
```