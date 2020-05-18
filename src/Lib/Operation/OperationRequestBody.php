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
        $class = $dto->class;

        if (!class_exists($class)) {
            return;
        }

        try {
            $instance = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $properties = DocBlockUtility::getProperties($instance);
        } catch (ReflectionException $e) {
            throw new SwaggerBakeRunTimeException('ReflectionException: ' . $e->getMessage());
        }

        if (empty($properties)) {
            return;
        }

        $filteredProperties = array_filter($properties, function ($property) use ($instance) {
            if (!isset($property->class) || $property->class != get_class($instance)) {
                return null;
            }
            return true;
        });

        if (empty($filteredProperties)) {
            return;
        }

        $requestBody = new RequestBody();
        $schema = (new Schema())->setType('object');

        foreach ($filteredProperties as $name => $reflectionProperty) {
            $docBlock = DocBlockUtility::getPropertyDocBlock($reflectionProperty);
            $vars = $docBlock->getTagsByName('var');
            if (empty($vars)) {
                throw new SwaggerBakeRunTimeException('@var must be set for ' . $class . '::' . $name);
            }
            $var = reset($vars);
            $dataType = DocBlockUtility::getDocBlockConvertedVar($var);

            $schema->pushProperty(
                (new SchemaProperty())
                    ->setDescription($docBlock->getSummary())
                    ->setName($name)
                    ->setType($dataType)
                    ->setRequired(!empty($docBlock->getTagsByName('required')))
            );
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

            $requestBody->pushContent(
                (new Content())
                    ->setMimeType($mimeType)
                    ->setSchema($this->schema)
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