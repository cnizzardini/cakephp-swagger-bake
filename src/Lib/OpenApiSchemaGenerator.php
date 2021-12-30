<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Model\ModelScanner;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\Schema\SchemaFactory;

class OpenApiSchemaGenerator
{
    /**
     * @param \SwaggerBake\Lib\Model\ModelScanner $modelScanner Model Scanner
     */
    public function __construct(
        private ModelScanner $modelScanner,
    ) {
    }

    /**
     * Builds schemas from cake models
     *
     * @param array $openapi The OpenAPI array
     * @return array
     * @throws \ReflectionException
     */
    public function generate(array $openapi = []): array
    {
        $schemaFactory = new SchemaFactory();
        $models = $this->modelScanner->getModelDecorators();

        foreach ($models as $model) {
            $entityName = (new \ReflectionClass($model->getModel()->getEntity()))->getShortName();

            if ($this->getSchemaByName($openapi, $entityName)) {
                continue;
            }

            $schema = $schemaFactory->create($model);
            if (!$schema) {
                continue;
            }

            if (in_array($schema->getVisibility(), [OpenApiSchema::VISIBILE_DEFAULT, OpenApiSchema::VISIBILE_ALWAYS])) {
                $openapi = $this->addSchema($openapi, $schema);
            } elseif ($schema->getVisibility() == OpenApiSchema::VISIBILE_HIDDEN) {
                $openapi = $this->addVendorSchema($openapi, $schema);
            }
        }

        return $openapi;
    }

    /**
     * Adds the Schema to the OpenAPI array
     *
     * @param array $openapi The OpenAPI array
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return array
     */
    private function addSchema(array $openapi, Schema $schema): array
    {
        $name = $schema->getName();
        if (!isset($openapi['components']['schemas'][$name])) {
            $openapi['components']['schemas'][$name] = $schema->setRefPath('#/components/schemas/' . $name);
        }

        return $openapi;
    }

    /**
     * Adds a Schema element to the OpenAPI array
     *
     * @param array $openapi The OpenAPI array
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema
     * @return array
     */
    private function addVendorSchema(array $openapi, Schema $schema): array
    {
        $name = $schema->getName();
        if (!isset($openapi['x-swagger-bake']['components']['schemas'][$name])) {
            $schema->setRefPath('#/x-swagger-bake/components/schemas/' . $name);
            $openapi['x-swagger-bake']['components']['schemas'][$name] = $schema;
        }

        return $openapi;
    }

    /**
     * Returns a schema object by $name argument
     *
     * @param array $openapi The OpenAPI array
     * @param string $name Name of schema
     * @return \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private function getSchemaByName(array $openapi, string $name): ?Schema
    {
        if (isset($openapi['components']['schemas'][$name])) {
            return $openapi['components']['schemas'][$name];
        }

        return $openapi['x-swagger-bake']['components']['schemas'][$name] ?? null;
    }
}
