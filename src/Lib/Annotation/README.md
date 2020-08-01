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
- [@SwagDtoForm](#swagdtoform)
- [@SwagHeader](#swagheader)
- [@SwagPathParameter](#swagpathparameter)
- [@SwagSecurity](#swagsecurity)
- [@SwagOperation](#swagoperation)
- [@SwagRequestBody](#swagrequestbody)
- [@SwagRequestBodyContent](#swagrequestbodycontent)
- [@SwagResponseSchema](#swagresponseschema)
- [@SwagPath](#swagpath)
- [@SwagSecurity](#swagsecurity)
- [@SwagOperation](#swagoperation)
- [@SwagRequestBody](#swagrequestbody)
- [@SwagRequestBodyContent](#swagrequestbodycontent)
- [@SwagResponseSchema](#swagresponseschema)
- [@SwagPath](#swagpath)
- [@SwagEntity](#swagentity)
- [@SwagEntityAttribute](#swagentityattribute)

## Adding Annotations

Annotations use the `doctrine/annotations` package. To add a new Annotation simply create a new class using one of the 
existing annotations as an example. Then add the new annotation to `src/Lib/AnnotationLoader.php`.

See the [Extension README](src/Lib/Extension/README.md) for details on extension. 

## Usage

You can improve this documentation by submitting PRs.

### @SwagPaginator
Method level annotation for adding [CakePHP Paginator](https://book.cakephp.org/4/en/controllers/components/pagination.html) 
query parameters: 

- page
- limit
- sort
- direction

```php
use SwaggerBake\Lib\Annotation as Swag;
/**
 * @Swag\SwagPaginator
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
          required: false
          schema:
            description: ''
            type: integer
          deprecated: false
          allowEmptyValue: false
          explode: false
          allowReserved: false
        - name: limit
          in: query
          required: false
          schema:
            description: ''
            type: integer
          deprecated: false
          allowEmptyValue: false
          explode: false
          allowReserved: false
        - name: sort
          in: query
          required: false
          schema:
            description: ''
            type: string
          deprecated: false
          allowEmptyValue: false
          explode: false
          allowReserved: false
        - name: direction
          in: query
          required: false
          schema:
            description: ''
            type: string
          deprecated: false
          allowEmptyValue: false
          explode: false
          allowReserved: false
```

### @SwagSearch
Method level annotation for documenting search parameters using the popular 
[friendsofcake/search](https://github.com/FriendsOfCake/search) plugin. Note, you must import `@SwagSearch` from  
`SwaggerBake\Lib\Extension\CakeSearch\Annotation`.

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
Method level annotation for adding query parameters. [Read the comments](src/Lib/Annotation/SwagQuery.php) 
to see all supported OpenAPI properties.

```php
/**
 * @Swag\SwagQuery(name="one", required=true, description="example description")
 * @Swag\SwagQuery(name="two", type="string", explode=true)
 * @Swag\SwagQuery(name="three", enum={"A","B","C"}, deprecated=true)
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
```

### @SwagForm
Method level annotation for adding form data fields. [Read the comments](src/Lib/Annotation/SwagForm.php) 
to see all supported OpenAPI properties.

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

```php
/**
 * @Swag\SwagDto(class="\App\Dto\ActorDto")
 */
public function index() {}
```

### @SwagDtoQuery
Property level annotation for use in your SwagDto classes. [Read the comments](src/Lib/Annotation/SwagDtoQuery.php) to 
see all supported properties.

```php
class ActorDto {
     /**
      * @SwagDtoQuery(name="example", type="string", required=true, enum={"A","B"})
      */
    private $example;
```

### @SwagDtoForm
Property level annotation for use in your SwagDto classes. [Read the comments](src/Lib/Annotation/SwagDtoForm.php) to 
see all supported properties.

```php
class ActorDto {
     /**
      * @SwagDtoForm(name="example", type="integer", required=true, minimum=10, maximum=100)
      */
    private $example;
```

### @SwagHeader
Method level annotation for adding header parameters. [Read the comments](src/Lib/Annotation/SwagHeader.php) 
to see all supported OpenAPI properties.

```php
/**
 * @Swag\SwagHeader(name="X-HEAD-ATTRIBUTE", type="string", description="example")
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
```

### @SwagPathParameter
Method level annotation for modifying path parameters. [Read the comments](src/Lib/Annotation/SwagPathParameter.php) 
to see all supported OpenAPI properties. This is for modifying existing path parameters only. Path parameters must 
first be defined in your routes file.

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

```php
/**
 * @Swag\SwagSecurity(name="BearerAuth", scopes={"Read","Write"})
 */
public function index() {}
```

### @SwagOperation
Method level annotation for OpenApi Operations. [Read the comments](src/Lib/Annotation/SwagOperation.php) for examples 
and further explanations.

```php
/**
 * @Swag\SwagOperation(isVisible=true, tagNames={"Custom","Tags"}, showPut=true)
 */
public function index() {}
```

```yaml
  put:
    tags:
      - Custom
      - Tags
```

### @SwagRequestBody
Method level annotation for describing request body. Set ignoreCakeSchema for full control over request body.

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

- `mimeType` has been deprecated in >= v1.5, use array form with `mimeTypes`

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
Method level annotation for defining response schema. [Read the comments](src/Lib/Annotation/SwagResponseSchema.php) to 
see all supported properties and additional examples.

- `mimeType` is deprecated in >= v1.5, use `mimeTypes` as an array.
- `httpCode` is deprecated in >= v1.3, use `statusCode` 

```php
/**
 * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Actor", description="Summary", statusCode="200")
 * @Swag\SwagResponseSchema(refEntity="#/components/schemas/Exception", mimeTypes={"application/xml"}, statusCode="40x")
 */
public function view() {}
```

```yaml
     responses:
       '200':                                    # note: default is 200
         content:
           application/json:                     # note: uses your default mime type since none was specified
             schema:
               description: My summary
               type: object                      # note: `object` is default when using refEntity
               $ref: '#/components/schemas/Actor'
       '40x':
         content:
           application/xml:
             schema:
               type: object
               items:
                 $ref: '#/components/schemas/Exception'
```

### @SwagPath
Class level annotation for exposing controllers to Swagger UI. You can hide entire controllers with this annotation.

```php
/**
 * @Swag\SwagPath(isVisible=false, description="optional description", summary="operational summary")
 */
class UsersController extends AppController {
```

### @SwagEntity
Class level annotation for exposing entities to Swagger UI. By default, all entities with routes will display as Swagger 
schema. You can hide a schema or display a schema that does not have an associated route.

```php
/**
 * @Swag\SwagEntity(isVisible=false, title="optional title", description="optional description")
 */
class Employee extends Entity {
```

### @SwagEntityAttribute
Class level annotation for customizing Schema Attributes. [Read the comments](src/Lib/Annotation/SwagEntityAttribute.php) 
to see all supported OpenAPI properties.

```php
/**
 * @Swag\SwagEntityAttribute(refEntity="example_one", type="string", minLength=5, maxLength=10)
 * @Swag\SwagEntityAttribute(refEntity="example_two", type="string", enum={"PG","R"}, required=true)
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
```