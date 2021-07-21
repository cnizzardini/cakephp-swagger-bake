<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagForm;
use SwaggerBake\Lib\Annotation\SwagRequestBody;
use SwaggerBake\Lib\Annotation\SwagRequestBodyContent;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\OpenApi\Xml;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;
use SwaggerBake\Lib\Utility\SchemaRefUtility;

/**
 * Class OperationRequestBody
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationRequestBody
{
    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    private $swagger;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Operation
     */
    private $operation;

    /**
     * @var \SwaggerBake\Lib\Route\RouteDecorator
     */
    private $route;

    /**
     * @var array
     */
    private $annotations;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private $schema;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param array $annotations Array of annotation objects
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema Schema
     */
    public function __construct(
        Swagger $swagger,
        Operation $operation,
        array $annotations,
        RouteDecorator $route,
        ?Schema $schema
    ) {
        $this->swagger = $swagger;
        $this->operation = $operation;
        $this->annotations = $annotations;
        $this->route = $route;
        $this->schema = $schema;
        $this->config = $swagger->getConfig();
    }

    /**
     * Gets an Operation with RequestBody
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithRequestBody(): Operation
    {
        if (!in_array($this->operation->getHttpMethod(), ['POST','PATCH','PUT'])) {
            return $this->operation;
        }

        $this->assignSwagRequestBodyAnnotation();
        $this->assignSwagRequestBodyContentAnnotations();
        $this->assignSwagFormAnnotations();
        $this->assignSwagDto();
        $this->assignSchema();

        return $this->operation;
    }

    /**
     * Assigns @SwagRequestBody annotations
     *
     * @return void
     */
    private function assignSwagRequestBodyAnnotation(): void
    {
        $swagRequestBodies = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagRequestBody;
        });

        if (empty($swagRequestBodies)) {
            return;
        }

        $swagRequestBody = reset($swagRequestBodies);

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        $requestBody
            ->setDescription($swagRequestBody->description)
            ->setRequired($swagRequestBody->required);

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Assigns @SwagRequestBodyContent annotations
     *
     * @return void
     */
    private function assignSwagRequestBodyContentAnnotations(): void
    {
        $swagRequestBodyContents = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagRequestBodyContent;
        });

        if (empty($swagRequestBodyContents)) {
            return;
        }

        $swagRequestBodyContent = reset($swagRequestBodyContents);

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        $mimeTypes = $this->config->getRequestAccepts();
        if (!empty($swagRequestBodyContent->mimeTypes)) {
            $mimeTypes = $swagRequestBodyContent->mimeTypes;
        }

        if (!empty($swagRequestBodyContent->refEntity)) {
            $pieces = explode('/', $swagRequestBodyContent->refEntity);
            $entity = end($pieces);
            $schema = $this->getSchemaWithWritablePropertiesOnly(
                $this->swagger->getSchemaByName($entity)
            );
        }

        foreach ($mimeTypes as $mimeType) {
            $content = (new Content())->setMimeType($mimeType);

            if (isset($schema)) {
                $content->setSchema($schema);
            } else {
                $content->setSchema($swagRequestBodyContent->refEntity);
            }

            $requestBody->pushContent($content);
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Adds @SwagForm annotations to the Operations Request Body
     *
     * @return void
     */
    private function assignSwagFormAnnotations(): void
    {
        $swagForms = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagForm;
        });

        if (empty($swagForms)) {
            return;
        }

        $schema = (new Schema())->setType('object');

        foreach ($swagForms as $annotation) {
            $schema->pushProperty(
                (new SchemaProperty())
                    ->setDescription($annotation->description ?? '')
                    ->setName($annotation->name)
                    ->setType($annotation->type)
                    ->setRequired($annotation->required)
                    ->setEnum($annotation->enum)
                    ->setDeprecated($annotation->deprecated)
            );
        }

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        foreach ($this->config->getRequestAccepts() as $mimeType) {
            $requestBody->pushContent(
                (new Content())
                    ->setMimeType($mimeType)
                    ->setSchema($schema)
            );
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Adds @SwagDto annotations to the Operations Request Body
     *
     * @return void
     * @throws \ReflectionException
     */
    private function assignSwagDto(): void
    {
        $swagDtos = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagDto;
        });

        if (empty($swagDtos)) {
            return;
        }

        $dto = reset($swagDtos);
        $fqn = $dto->class;

        if (!class_exists($fqn)) {
            return;
        }

        $requestBody = new RequestBody();
        $schema = (new Schema())->setType('object');

        $properties = (new DtoParser($fqn))->getSchemaProperties();
        foreach ($properties as $property) {
            $schema->pushProperty($property);
        }

        foreach ($this->config->getRequestAccepts() as $mimeType) {
            $requestBody->pushContent(
                (new Content())
                    ->setMimeType($mimeType)
                    ->setSchema($this->applyRootNodeToXmlSchema($schema, $mimeType))
            );
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Adds Schema to the Operations Request Body
     *
     * @return void
     */
    private function assignSchema(): void
    {
        $ignoreSchemas = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagRequestBody && $annotation->ignoreCakeSchema === true;
        });

        if (!empty($ignoreSchemas) || !$this->schema) {
            return;
        }

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();
        $requestBody->setRequired($this->isCrudAction());

        foreach ($this->config->getRequestAccepts() as $mimeType) {
            if ($requestBody->getContentByType($mimeType)) {
                continue;
            }

            $schema = clone $this->schema;
            $schema = $this->applyRootNodeToXmlSchema($schema, $mimeType, $schema->getName());

            $content = (new Content())->setMimeType($mimeType);

            if (in_array($this->operation->getHttpMethod(), ['POST'])) {
                $content->setSchema(
                    SchemaRefUtility::whichRef($schema, $this->swagger, $this->schema->getAddSchemaRef())
                );
            } else {
                $content->setSchema(
                    SchemaRefUtility::whichRef($schema, $this->swagger, $this->schema->getEditSchemaRef())
                );
            }

            $requestBody->pushContent($content);
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

            if (count(array_intersect($httpMethods, ['PUT','PATCH'])) > 1 && $requireOnUpdate) {
                $schemaProperty->setRequired(true);
            } elseif (count(array_intersect($httpMethods, ['POST'])) > 1 && $requireOnCreate) {
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
