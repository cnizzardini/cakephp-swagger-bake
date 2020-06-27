<?php

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use SwaggerBake\Lib\Annotation\SwagForm;
use SwaggerBake\Lib\Annotation\SwagDto;
use SwaggerBake\Lib\Annotation\SwagRequestBody;
use SwaggerBake\Lib\Annotation\SwagRequestBodyContent;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\RequestBody;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\SchemaProperty;
use SwaggerBake\Lib\Utility\DocBlockUtility;

/**
 * Class OperationRequestBody
 * @package SwaggerBake\Lib\Operation
 */
class OperationRequestBody
{
    /** @var Configuration  */
    private $config;

    /** @var Operation  */
    private $operation;

    /** @var DocBlock  */
    private $doc;

    /** @var RouteDecorator  */
    private $route;

    /** @var array  */
    private $annotations;

    /** @var Schema|null  */
    private $schema;

    public function __construct(
        Configuration $config,
        Operation $operation,
        DocBlock $doc,
        array $annotations,
        RouteDecorator $route,
        ?Schema $schema
    ) {
        $this->config = $config;
        $this->operation = $operation;
        $this->doc = $doc;
        $this->annotations = $annotations;
        $this->route = $route;
        $this->schema = $schema;
    }

    /**
     * Gets an Operation with RequestBody
     *
     * @return Operation
     */
    public function getOperationWithRequestBody() : Operation
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
    private function assignSwagRequestBodyAnnotation() : void
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
            ->setRequired($swagRequestBody->required)
        ;

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Assigns @SwagRequestBodyContent annotations
     *
     * @return void
     */
    private function assignSwagRequestBodyContentAnnotations() : void
    {
        $swagRequestBodyContents = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagRequestBodyContent;
        });

        if (empty($swagRequestBodyContents)) {
            return;
        }

        $swagRequestBodyContent = reset($swagRequestBodyContents);

        $requestBody = $this->operation->getRequestBody() ?? new RequestBody();

        $requestBody->pushContent(
            (new Content())
                ->setMimeType($swagRequestBodyContent->mimeType)
                ->setSchema($swagRequestBodyContent->refEntity)
        );

        $this->operation->setRequestBody($requestBody);
    }

    /**
     * Adds @SwagForm annotations to the Operations Request Body
     *
     * @return void
     */
    private function assignSwagFormAnnotations() : void
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
                    ->setDescription($annotation->description)
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
     * @return void
     * @throws ReflectionException
     */
    private function assignSwagDto() : void
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
    private function assignSchema() : void
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

            $schema = clone $this->schema;
            $schemaProperties = array_filter($schema->getProperties(), function ($property) {
                return $property->isReadOnly() === false;
            });

            foreach ($schemaProperties as $schemaProperty) {
                if ($this->route->getAction() == 'edit' && $schemaProperty->isRequirePresenceOnUpdate()) {
                    $schemaProperty->setRequired(true);
                } elseif ($this->route->getAction() == 'add' && $schemaProperty->isRequirePresenceOnCreate()) {
                    $schemaProperty->setRequired(true);
                }
                $schema->pushProperty($schemaProperty);
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
     * @param RequestBody $requestBody
     * @return RequestBody
     */
    private function getRequestBodyWithFormSchema(RequestBody $requestBody) : RequestBody
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

        $schema = clone $this->schema;
        $schemaProperties = array_filter($schema->getProperties(), function ($property) {
            return $property->isReadOnly() === false;
        });

        foreach ($schemaProperties as $schemaProperty) {
            if ($this->route->getAction() == 'edit' && $schemaProperty->isRequirePresenceOnUpdate()) {
                $schemaProperty->setRequired(true);
            } elseif ($this->route->getAction() == 'add' && $schemaProperty->isRequirePresenceOnCreate()) {
                $schemaProperty->setRequired(true);
            }
        }

        $properties = array_merge($schemaProperties, $properties);

        $schema->setProperties($properties);

        $requestBody->pushContent(
            (new Content())
                ->setMimeType('application/x-www-form-urlencoded')
                ->setSchema($schema)
        );

        return $requestBody;
    }
}