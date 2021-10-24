<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use ReflectionMethod;
use SwaggerBake\Lib\Attribute\AttributeFactory;
use SwaggerBake\Lib\Attribute\OpenApiDto;
use SwaggerBake\Lib\Attribute\OpenApiForm;
use SwaggerBake\Lib\Attribute\OpenApiRequestBody;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
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
        private ?Schema $schema = null
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
            OpenApiRequestBody::class
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
            $schema = $this->getSchemaWithWritablePropertiesOnly(
                $this->swagger->getSchemaByName($entity)
            );
        }

        foreach ($mimeTypes as $mimeType) {
            $requestBody->pushContent(new Content($mimeType, $schema ?? $openApiRequestBody->ref));
        }

        $this->operation->setRequestBody(
            $openApiRequestBody->createRequestBody($requestBody)
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
        /** @var \SwaggerBake\Lib\Attribute\OpenApiForm[] $openApiForms */
        $openApiForms = (new AttributeFactory(
            $this->refMethod,
            OpenApiForm::class
        ))->createMany();

        if (empty($openApiForms)) {
            return;
        }

        $schema = (new Schema())->setType('object');

        foreach ($openApiForms as $attribute) {
            $schema->pushProperty(
                $attribute->create()
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
            OpenApiDto::class
        ))->createOneOrNull();

        if (!$openApiDto instanceof OpenApiDto || !class_exists($openApiDto->class)) {
            return;
        }

        $requestBody = new RequestBody();
        $schema = (new Schema())->setType('object');

        $properties = (new DtoParser($openApiDto->class))->getSchemaProperties();
        foreach ($properties as $property) {
            $schema->pushProperty($property);
        }

        foreach ($this->config->getRequestAccepts() as $mimeType) {
            $requestBody->pushContent(new Content($mimeType, $this->applyRootNodeToXmlSchema($schema, $mimeType)));
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Apply default schema and OpenApiRequestBody to each mimetype
     *
     * @return void
     * @throws \ReflectionException
     * @todo Reflector to improve PHPMD scoring
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function applySchema(): void
    {
        if (!$this->schema) {
            return;
        }

        if ($this->refMethod instanceof ReflectionMethod) {
            $openApiRequestBody = (new AttributeFactory(
                $this->refMethod,
                OpenApiRequestBody::class
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

            $isPost = in_array($this->operation->getHttpMethod(), ['POST']);

            if ($isPost && $this->swagger->getSchemaByName($schema->getAddSchemaName())) {
                $contentSchema = $this->swagger->getSchemaByName($schema->getAddSchemaName())->getRefPath();
            } elseif ($this->swagger->getSchemaByName($schema->getEditSchemaName())) {
                $contentSchema = $this->swagger->getSchemaByName($schema->getEditSchemaName())->getRefPath();
            } else {
                $contentSchema = $this->swagger->getSchemaByName($schema->getName())->getRefPath();
            }

            $requestBody->pushContent(new Content($mimeType, $contentSchema));
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Returns new Schema instance with only writable properties
     *
     * @param \SwaggerBake\Lib\OpenApi\Schema $schema instance of Schema
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function getSchemaWithWritablePropertiesOnly(Schema $schema): Schema
    {
        $newSchema = clone $schema;
        $newSchema->setProperties([]);

        $schemaProperties = array_filter($schema->getProperties(), function ($property) {
            return $property->isReadOnly() === false;
        });

        $httpMethods = $this->route->getMethods();

        foreach ($schemaProperties as $schemaProperty) {
            $requireOnUpdate = $schemaProperty->isRequirePresenceOnUpdate();
            $requireOnCreate = $schemaProperty->isRequirePresenceOnCreate();
            $hasRequestBody = count(array_intersect($httpMethods, ['POST','PUT', 'PATCH'])) > 1;
            if ($hasRequestBody && ($requireOnUpdate || $requireOnCreate)) {
                $schemaProperty->setRequired(true);
            }
            $newSchema->pushProperty($schemaProperty);
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
    private function applyRootNodeToXmlSchema(Schema $schema, string $mimeType, ?string $name = 'request')
    {
        if ($mimeType !== 'application/xml') {
            return $schema;
        }

        return $schema->setXml((new Xml())->setName($name));
    }
}
