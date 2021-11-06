# SwaggerBake Extensions

Extensions to SwaggerBake can be added through the use of events and OpenAPI vendor extensions.

See the [CakeSearch extension](https://github.com/cnizzardini/cakephp-swagger-bake/tree/master/src/Lib/Extension) for 
an example. You may submit extensions as PRs to this project.

1. Your extension must implement ExtensionInterface. Read the interfaces comments and refer to the CakeSearch
   extension for additional insight.

2. Add your extension to the `EXTENSIONS` constant in `src/Lib/ExtensionLoader.php`.

3. Create necessary unit tests `tests/TestCase/Lib/Extension`.

4. Submit your PR!

## Custom Collection Schemas

You can modify the schema of your `application/json` or `application/xml` collection responses with a custom collection 
schema. Modify your base OpenAPI YAML file to include `#/x-swagger-bake/components/schemas/Generic-Collection`. Example:

```
x-swagger-bake:
  components:
    schemas:
      Generic-Collection:
        type: object
        x-data-element: data # property or node that contains the collections items (records)
        properties:
          collection: # sample of a property holding pagination data
            type: object
            properties:
              url:
                type: string
                format: url
                example: /index
              count:
                type: integer
                example: 20
              total:
                type: integer
                example: 200
              pages:
                type: integer
                example: 10
              next:
                type: string
                format: url
                example: /index?page=:number
              prev:
                type: string
                format: url
                example: /index?page=:number
              first:
                type: string
                format: url
                example: /index
              last:
                type: string
                format: url
                example: /index?page=:number
```

You would need to implement the sample schema in our application still. See 
[MixerApi/CollectionView](https://github.com/mixerapi/collection-view) for a ready-made implementation.
