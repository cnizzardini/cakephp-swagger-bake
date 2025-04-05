<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionClass;
use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiDto;
use SwaggerBake\Lib\Attribute\OpenApiForm;
use SwaggerBake\Lib\Attribute\OpenApiRequestBody;
use SwaggerBake\Lib\Attribute\OpenApiSchema;
use SwaggerBake\Lib\Attribute\OpenApiSchemaProperty;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\OpenApi\Xml;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;

class OperationRequestBody
{
    private Configuration $config;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \ReflectionMethod|null $refMethod ReflectionMethod or null, this is used to access attributes
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema Schema or null, this will be used as the operations schema
     */
    public function __construct(
        private Swagger $swagger,
        private Operation $operation,
        private RouteDecorator $route,
        private ?ReflectionMethod $refMethod = null,
        private ?Schema $schema = null,
    ) {
        $this->config = $swagger->getConfig();
    }

    /**
     * Returns the Operation after applying various Attributes and Schemas
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     * @throws \ReflectionException
     */
    public function getOperationWithRequestBody(): Operation
    {
        if (!in_array($this->operation->getHttpMethod(), ['POST','PATCH','PUT'])) {
            return $this->operation;
        }
        if ($this->refMethod instanceof ReflectionMethod) {
            $this->applyRequestBody();
            $this->applyForm();
            $this->applyDataTransferObject();
        }
        $this->applySchema();

        return $this->operation;
    }

    /**
     * Apply OpenApiRequestBody attribute
     *
     * @return void
     * @throws \ReflectionException
     */
    private function applyRequestBody(): void
    {
        $openApiRequestBody = (new AttributeFactory(
            $this->refMethod,
            OpenApiRequestBody::class,
        ))->createOneOrNull();

        if (!$openApiRequestBody instanceof OpenApiRequestBody) {
            return;
        }

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        $mimeTypes = $this->config->getRequestAccepts();
        if (!empty($openApiRequestBody->mimeTypes)) {
            $mimeTypes = $openApiRequestBody->mimeTypes;
        }

        if (!empty($openApiRequestBody->ref)) {
            $pieces = explode('/', $openApiRequestBody->ref);
            $entity = end($pieces);
            $schema = $this->getSchemaWithRequiredProperties(
                $this->swagger->getSchemaByName($entity),
            );
        }

        foreach ($mimeTypes as $mimeType) {
            $requestBody->pushContent(new Content($mimeType, $schema ?? $openApiRequestBody->ref));
        }

        $this->operation->setRequestBody(
            $openApiRequestBody->createRequestBody($requestBody),
        );
    }

    /**
     * Apply OpenApiForm attributes
     *
     * @return void
     * @throws \ReflectionException
     */
    private function applyForm(): void
    {
        /** @var array<\SwaggerBake\Lib\Attribute\OpenApiForm> $openApiForms */
        $openApiForms = (new AttributeFactory(
            $this->refMethod,
            OpenApiForm::class,
        ))->createMany();

        if (empty($openApiForms)) {
            return;
        }

        $schema = (new Schema())->setType('object');

        foreach ($openApiForms as $attribute) {
            $schema->pushProperty(
                $attribute->create(),
            );
        }

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        foreach ($this->config->getRequestAccepts() as $mimeType) {
            $requestBody->pushContent(new Content($mimeType, $schema));
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Apply OpenApiDto attribute
     *
     * @return void
     * @throws \ReflectionException
     */
    private function applyDataTransferObject(): void
    {
        $openApiDto = (new AttributeFactory(
            $this->refMethod,
            OpenApiDto::class,
        ))->createOneOrNull();

        if (!$openApiDto instanceof OpenApiDto || !class_exists($openApiDto->class)) {
            return;
        }

        // check if the DTO contains the OpenApiSchema attribute
        $dtoReflection = new ReflectionClass($openApiDto->class);
        $openApiSchema = (new AttributeFactory(
            $dtoReflection,
            OpenApiSchema::class,
        ))->createOneOrNull();

        // add schema to #/components/schemas ?
        if ($openApiSchema instanceof OpenApiSchema) {
            $schema = $openApiSchema->createSchema();
        } else {
            $schema = (new Schema())->setVisibility(OpenApiSchema::VISIBLE_NEVER);
        }

        $schema
            ->setType('object')
            ->setName($dtoReflection->getShortName())
            ->setIsCustomSchema(true);

        // get openapi schema properties defined on the class
        $schemaProperties = (new AttributeFactory(
            $dtoReflection,
            OpenApiSchemaProperty::class,
        ))->createMany();
        /** @var array<\SwaggerBake\Lib\Attribute\OpenApiSchemaProperty> $schemaProperties */
        $properties = array_map(function ($prop) {
            return $prop->create();
        }, $schemaProperties);

        // get openapi schema properties defined per class property
        $properties = array_merge($properties, (new DtoParser($dtoReflection))->getSchemaProperties());
        foreach ($properties as $property) {
            $schema->pushProperty($property);
        }

        $requestBody = new RequestBody();
        foreach ($this->config->getRequestAccepts() as $mimeType) {
            $schema = $this->applyRootNodeToXmlSchema($schema, $mimeType);
            $requestBody->pushContent(new Content($mimeType, $schema));
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Apply default schema and OpenApiRequestBody to each mimetype
     *
     * @return void
     * @throws \ReflectionException
     */
    private function applySchema(): void
    {
        if (!$this->schema) {
            return;
        }

        if ($this->refMethod instanceof ReflectionMethod) {
            $openApiRequestBody = (new AttributeFactory(
                $this->refMethod,
                OpenApiRequestBody::class,
            ))->createOneOrNull();
        }

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        if (isset($openApiRequestBody) && $openApiRequestBody instanceof OpenApiRequestBody) {
            if ($openApiRequestBody->ignoreCakeSchema) {
                return;
            }
            $requestBody = $openApiRequestBody
                ->createRequestBody($requestBody)
                ->setRequired($this->isCrudAction());
        }

        foreach ($this->config->getRequestAccepts() as $mimeType) {
            if ($requestBody->getContentByType($mimeType)) {
                continue;
            }

            $schema = clone $this->schema;
            $schema = $this->applyRootNodeToXmlSchema($schema, $mimeType, $schema->getName());
            $schema = $this->getSchemaWithRequiredProperties($schema);

            $requestBody->pushContent(new Content($mimeType, $schema));
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Returns new Schema instance with required properties
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema instance of Schema
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function getSchemaWithRequiredProperties(Schema $schema): Schema
    {
        $newSchema = clone $schema;
        $isUpdate = count(array_intersect($this->route->getMethods(), ['PATCH'])) >= 1;
        $isCreate = count(array_intersect($this->route->getMethods(), ['POST', 'PUT'])) >= 1;

        /** @var \SwaggerBake\Lib\OpenApi\SchemaProperty|\SwaggerBake\Lib\OpenApi\Schema $property */
        foreach ($newSchema->getProperties() as $property) {
            if ($property instanceof SchemaProperty) {
                if ($isUpdate && $property->isRequirePresenceOnUpdate()) {
                    $newSchema->pushRequired($property->getName());
                } elseif ($isCreate && $property->isRequirePresenceOnCreate()) {
                    $newSchema->pushRequired($property->getName());
                }
            }
        }

        return $newSchema;
    }

    /**
     * Does the route represent a CRUD action (add, edit, view, delete, index)?
     *
     * @return bool
     */
    private function isCrudAction(): bool
    {
        return in_array($this->route->getAction(), ['add','edit','view','delete','index']);
    }

    /**
     * Applies a root node to XML schemas (required for XML example in Swagger)
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema Schema instance
     * @param string $mimeType A mimetype such as application/xml
     * @param string|null $name Name for the XML root node
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function applyRootNodeToXmlSchema(Schema $schema, string $mimeType, ?string $name = 'request'): Schema
    {
        if ($mimeType !== 'application/xml') {
            return $schema;
        }

        return $schema->setXml((new Xml())->setName($name));
    }
}
