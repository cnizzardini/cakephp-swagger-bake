<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagForm;
use SwaggerBake\Lib\Annotation\SwagRequestBody;
use SwaggerBake\Lib\Annotation\SwagRequestBodyContent;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\OpenApi\Xml;
use SwaggerBake\Lib\Swagger;

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
     * @var \SwaggerBake\Lib\Decorator\RouteDecorator
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
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator $route RouteDecorator
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

        $requestBody->pushContent(
            (new Content())
                ->setMimeType('application/x-www-form-urlencoded')
                ->setSchema($schema)
        );

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
        $fqns = $dto->class;

        if (!class_exists($fqns)) {
            return;
        }

        $requestBody = new RequestBody();
        $schema = (new Schema())->setType('object');

        $properties = (new DtoParser($fqns))->getSchemaProperties();
        foreach ($properties as $property) {
            $schema->pushProperty($property);
        }

        $this->operation->setRequestBody(
            $requestBody->pushContent(
                (new Content())
                    ->setMimeType('application/x-www-form-urlencoded')
                    ->setSchema($schema)
            )
        );
    }

    /**
     * Adds Schema to the Operations Request Body
     *
     * @return void
     */
    private function assignSchema(): void
    {
        if (!$this->schema) {
            return;
        }

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        foreach ($this->config->getRequestAccepts() as $mimeType) {
            if ($mimeType === 'application/x-www-form-urlencoded') {
                $requestBody = $this->getRequestBodyWithFormSchema($requestBody);
                continue;
            }

            if ($requestBody->getContentByType($mimeType)) {
                continue;
            }

            $schema = $this->getSchemaWithWritablePropertiesOnly($this->schema);

            if ($mimeType == 'application/xml') {
                $schema->setXml(
                    (new Xml())->setName(strtolower($this->schema->getName()))
                );
            }

            $requestBody->pushContent(
                (new Content())
                    ->setMimeType($mimeType)
                    ->setSchema($schema)
            );
        }

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Adds Schema to the Operations Request Body as application/x-www-form-urlencoded
     *
     * @param \SwaggerBake\Lib\OpenApi\RequestBody $requestBody RequestBody
     * @return \SwaggerBake\Lib\OpenApi\RequestBody
     */
    private function getRequestBodyWithFormSchema(RequestBody $requestBody): RequestBody
    {
        $ignoreSchemas = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagRequestBody && $annotation->ignoreCakeSchema === true;
        });

        if (!empty($ignoreSchemas) || !isset($this->schema)) {
            return $requestBody;
        }

        $properties = [];
        if ($requestBody->getContentByType('application/x-www-form-urlencoded')) {
            $properties = $requestBody
                ->getContentByType('application/x-www-form-urlencoded')
                ->getSchema()
                ->getProperties();
        }

        $schema = $this->getSchemaWithWritablePropertiesOnly($this->schema);

        $properties = array_merge($schema->getProperties(), $properties);

        $schema->setProperties($properties);

        $requestBody->pushContent(
            (new Content())
                ->setMimeType('application/x-www-form-urlencoded')
                ->setSchema($schema)
        );

        return $requestBody;
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
}
