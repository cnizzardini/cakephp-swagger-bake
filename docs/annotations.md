# SwaggerBake Annotations

SwaggerBake provides some optional Annotations for enhanced functionality. There are core annotations and extended 
annotations which support other Cake plugins. These can be imported individually or set to an alias as seen below.

- `use SwaggerBake\Lib\Annotation as Swag;`
- `use SwaggerBake\Lib\Extension as SwagExt;`

## Table of Contents
- [Adding Annotations](#adding-annotations)
- [Usage](#usage)
- [@SwagPaginator](#swagpaginator)
- [@SwagSearch](#swagsearch)
- [@SwagQuery](#swagquery)
- [@SwagForm](#swagform)
- [@SwagDto](#swagdto)
- [@SwagDtoQuery](#swagdtoquery)
- [@SwagDtoRequestBody](#swagdtorequestbody)
- [@SwagHeader](#swagheader)
- [@SwagPathParameter](#swagpathparameter)
- [@SwagSecurity](#swagsecurity)
- [@SwagOperation](#swagoperation)
- [@SwagRequestBody](#swagrequestbody)
- [@SwagRequestBodyContent](#swagrequestbodycontent)
- [@SwagResponseSchema](#swagresponseschema)
- [@SwagPath](#swagpath)
- [@SwagEntity](#swagentity)
- [@SwagEntityAttribute](#swagentityattribute)

## Usage

You can improve this documentation by submitting PRs.

### @SwagPaginator
Method level annotation for adding [CakePHP Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html) 
query parameters: page, limit, sort, and direction. When specified with no arguments, Paginate.sortableFields will be 
used to populate the options list (for `index()` actions only). User supplied options can be given using sortEnum. 

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| sortEnum | array `[]` | A list of fields that can be sorted by. |
| useSortTextInput | boolean `false` | Use an input box instead of dropdown for sortable field |

```php
/**
 * @Swag\SwagPaginator(sortEnum={"id","name"})
 */
public function index() {
    $employees = $this->paginate($this->Employees);
    $this->set(compact('employees'));
    $this->viewBuilder()->setOption('serialize', ['employees']);
}
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

### @SwagSearch
Method level annotation for documenting search parameters using the popular 
[friendsofcake/search](https://github.com/FriendsOfCake/search) plugin.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| tableClass | string | Required FQN to the Table class |
| collection | string `default` | The Cake Search collection _(see vendor documentation)_ |

```php
/**
 * @SwagExt\CakeSearch\Annotation\SwagSearch(tableClass="\App\Model\Table\FilmsTable", collection="default")
 */
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

### @SwagQuery 
Method level annotation for adding query parameters.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the query parameter |
| type | string `string` | Data type |
| description | string `""` | Description of the parameter |
| required | bool `false` | Is this parameter required? |
| enum | array `[]` | An enumerated list of accepted values |
| deprecated | bool `false` | Is this parameter deprecated? |
| allowReserved | bool `false` | Allow reserved URI characters? |
| allowEmptyValue | bool `false` | Allow empty values? |
| explode | bool `false` | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| style | string `""` | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| format | string `""` | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |
| example | mixed `null` | http://spec.openapis.org/oas/v3.0.3#fixed-fields-9 |


```php
/**
 * @Swag\SwagQuery(name="one", required=true, description="example description")
 * @Swag\SwagQuery(name="two", type="string", explode=true)
 * @Swag\SwagQuery(name="three", enum={"A","B","C"}, deprecated=true)
 * @Swag\SwagQuery(ref="#/x-my-project/components/parameters/my-parameter")
 */
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

### @SwagForm
Method level annotation for adding form data fields.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the schema property |
| type | string `string` | Date type such as integer, string, etc... |
| format | string `""` | Date format such as int32, date-time, etc... |
| description | string `""` | Description of the property |
| readOnly | bool `false` | Is the property read only? |
| writeOnly | bool `false` | Is the property write only? |
| required | bool `false` | Is the property required? |
| multipleOf | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maximum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| exclusiveMaximum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| minimum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| exclusiveMinimum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| pattern | string `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| uniqueItems | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| enum | array `[]` | http://spec.openapis.org/oas/v3.0.3#properties |

```php
/**
 * @Swag\SwagForm(name="one", type="string", description="example", required=true, enum={"A","B"})
 * @Swag\SwagForm(name="two", type="integer", minimum=10, maximum=100, multipleOf=10)
 * @Swag\SwagForm(name="three", type="string", minLength=8, maxLength=64, pattern="[a-zA-Z]")
 */
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

### @SwagDto
Method level annotation for building query or form parameters from a DataTransferObject. DTOs are more than just a 
best practice. Using them with SwaggerBake greatly reduces the amount of annotations you need to write. Consider 
using a DTO in place of SwagQuery or SwagForm. SwagDto uses either SwagDtoProperty or your existing Doc Blocks to 
build swagger query and post parameters.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| class | string | FQN of the DTO class |

```php
/**
 * @Swag\SwagDto(class="\App\Dto\ActorDto")
 */
public function index() {}
```

### @SwagDtoQuery
Property level annotation for use in your SwagDto classes.

```php
class ActorDto {
     /**
      * @SwagDtoQuery(name="example", type="string", required=true, enum={"A","B"})
      */
    private $example;
```

### @SwagDtoRequestBody
Formerly `@SwagDtoForm`. Property level annotation for use in your SwagDto classes.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the schema property |
| type | string `string` | Date type such as integer, string, etc... |
| format | string `""` | Date format such as int32, date-time, etc... |
| description | string `""` | Description of the property |
| readOnly | bool `false` | Is the property read only? |
| writeOnly | bool `false` | Is the property write only? |
| required | bool `false` | Is the property required? |
| multipleOf | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maximum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| exclusiveMaximum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| minimum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| exclusiveMinimum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| pattern | string `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| uniqueItems | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| enum | array `[]` | http://spec.openapis.org/oas/v3.0.3#properties |

```php
class ActorDto {
     /**
      * @SwagDtoRequestBody(name="example", type="integer", required=true, minimum=10, maximum=100)
      */
    private $example;
```

### @SwagHeader
Method level annotation for adding header parameters.
```php
/**
 * @Swag\SwagHeader(name="X-HEAD-ATTRIBUTE", type="string", description="example")
 * @Swag\SwagHeader(ref="#/x-my-project/components/parameters/my-header") 
 */
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

### @SwagPathParameter
Method level annotation for modifying path parameters. This is for modifying existing path parameters only. Path 
parameters must first be defined in your routes file.

```php
/**
 * @Swag\SwagPathParameter(name="id", type="integer", format="int64", description="ID")
 */
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

### @SwagSecurity
Method level annotation for adding authentication requirements. This annotation takes precedence over settings that 
SwaggerBake gathers from AuthenticationComponent. See the main documentation for more information.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the security option |
| scopes | array | Security Scopes |

```php
/**
 * @Swag\SwagSecurity(name="BearerAuth", scopes={"Read","Write"})
 */
public function index() {}
```

### @SwagOperation
Method level annotation for OpenApi Operations. 

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| isVisible | bool `true` | Is the operation visible? |
| tagNames | array `[]` | https://swagger.io/docs/specification/grouping-operations-with-tags/ |
| showPut | bool `false` | Add PUT operations to OpenAPI? By default on PATCH operations are shown |

```php
/**
 * @Swag\SwagOperation(isVisible=true, tagNames={"Custom","Tags"}, showPut=true)
 */
public function index() {}
```

OpenAPI:

```yaml
  put:
    tags:
      - Custom
      - Tags
```

### @SwagRequestBody
Method level annotation for describing request body. Set ignoreCakeSchema for full control over request body.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| description | string `""` | Description of the request body |
| required | bool `true` | Is the request body required? |
| ignoreCakeSchema | bool `false` | Ignore cake schema |

```php
/**
 * @Swag\SwagRequestBody(description="my description", required=true, ignoreCakeSchema=true)
 */
public function index() {}
```

### @SwagRequestBodyContent
Method level annotation for describing custom content in request body. The `mimeTypes` parameter is optional. If empty, 
all mimeTypes defined as `requestAccepts` in your swagger_bake.php will be used. This will only show writeable 
properties.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| refEntity | string `""` | The OpenAPI entity |
| mimeTypes | array `[]` | An array of strings of mime types to support |

```php
/**
 * @Swag\SwagRequestBodyContent(refEntity="#/components/schemas/Actor", mimeTypes={"application/json"})
 */
public function index() {}
```

OpenAPI:

```yaml
      requestBody:
        description: ''
        content:
          application/json:
            schema:
              description: Actor Entity
              type: object
              properties:
                first_name:
                  type: string
                  minLength: 1
                  maxLength: 45
                last_name:
                  type: string
                  minLength: 1
                  maxLength: 45
```

### @SwagResponseSchema
Method level annotation for defining response schema.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| refEntity | string `""` | The OpenAPI entity |
| statusCode | string `200` | Response code |
| mimeTypes | array `[]` | An array of mime types the response can be |
| description | string `null` | Description of the response |
| schemaType | string `null` |  |
| schemaFormat | string `null` |  |
| schemaItems | array `[]` |  |


```php
/**
 * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Actor", description="Summary", statusCode="200")
 */
public function view() {}
```

OpenAPI:

```yaml
     responses:
       '200':                                    # note: default is 200
         content:
           application/json:                     # note: uses your default mime type since none was specified
             schema:
               description: My summary
               type: object                      # note: `object` is default when using refEntity
               $ref: '#/components/schemas/Actor'
```

Defining a single mimeType and 400-409 status code range:

```php
/**
 * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Exception", mimeTypes={"application/xml"}, statusCode="40x")
 */
```

OpenAPI:

```yaml
       '40x':
         content:
           application/xml:
             schema:
               type: object
               items:
                 $ref: '#/components/schemas/Exception'
```

Defining an array of objects:

```php
/**
 * @Swag\SwagResponseSchema(schemaItems={"$ref"="#/components/schemas/Actor"})
 */
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
```

### @SwagPath
Class level annotation for exposing controllers to Swagger UI. You can hide entire controllers with this annotation.

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| isVisible | boolean `true` | Is the path and its operations visible in OpenAPI  |
| title | string `""` | Overwrites the default title |
| summary | string `""` | Overwrites the default summary (if any) |

```php
/**
 * @Swag\SwagPath(isVisible=false, description="optional description", summary="operational summary")
 */
class UsersController extends AppController {
```

### @SwagEntity
Class level annotation for exposing entities to Swagger UI. 

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| isVisible | boolean `true` | All entities with routes are added to OpenAPI schema. To completely hide a schema from appearing anywhere in OpenAPI JSON output set to false |
| isPublic | boolean `true` | To hide from the default via in Swagger 3.0 set to false. isVisible takes precedence (see isVisible vs isPublic below) |
| title | string `""` | Overwrites the default title |
| description | string `""` | Overwrites the default description (if any) |

**isVisible vs isPublic:** 

`isVisible` takes precedence over `isPublic`. If you've set `isVisible` to `false` then whatever you've defined for 
`isPublic` becomes inert. If a schema is visible, but not public it be accessed via 
`#/x-swagger-bake-bake/components/schemas/EntityName`. This is helpful if you want to reduce cluter in your Swagger 
schemas, but still want the ability to reference it via `@SwagResponseSchema`

```php
/**
 * @Swag\SwagEntity(isVisible=true, isPublic=false, title="optional title", description="optional description")
 */
class Employee extends Entity {
```

### @SwagEntityAttribute
Class level annotation for customizing Schema Attributes. Note that the attribute does not have to exist in your entity. 
You can add adhoc attributes as needed and optionally combine with 
[Virtual Fields](https://book.cakephp.org/4/en/orm/entities.html#creating-virtual-fields).

| Attribute | Type / Default | Description | 
| ------------- | ------------- | ------------- |
| name | string | Name of the schema property |
| type | string `string` | Date type such as integer, string, etc... |
| format | string `""` | Date format such as int32, date-time, etc... |
| description | string `""` | Description of the property |
| readOnly | bool `false` | Is the property read only? |
| writeOnly | bool `false` | Is the property write only? |
| required | bool `false` | Is the property required? |
| multipleOf | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maximum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| exclusiveMaximum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| minimum | float `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| exclusiveMinimum | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minLength | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| pattern | string `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minItems | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| uniqueItems | bool `false` | http://spec.openapis.org/oas/v3.0.3#properties |
| maxProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| minProperties | integer `null` | http://spec.openapis.org/oas/v3.0.3#properties |
| enum | array `[]` | http://spec.openapis.org/oas/v3.0.3#properties |
| example | mixed `null` | http://spec.openapis.org/oas/v3.0.3#properties |

```php
/**
 * @Swag\SwagEntityAttribute(name="example_one", type="string", minLength=5, maxLength=10)
 * @Swag\SwagEntityAttribute(name="example_two", type="string", enum={"PG","R"}, required=true)
 * @Swag\SwagEntityAttribute(name="example_virtual_field", type="string", readOnly=true)
 */
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

## Adding Annotations

Annotations use the `doctrine/annotations` package. To add a new Annotation simply create a new class using one of the 
existing annotations as an example. Then add the new annotation to `src/Lib/AnnotationLoader.php`.