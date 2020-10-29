<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\MediaType\Generic;
use SwaggerBake\Lib\MediaType\HalJson;
use SwaggerBake\Lib\MediaType\JsonLd;
use SwaggerBake\Lib\MediaType\Xml as XmlMedia;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\Xml;
use SwaggerBake\Lib\Route\RouteDecorator;
use SwaggerBake\Lib\Swagger;

/**
 * Class OperationResponse
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationResponse
{
    /**
     * @var \SwaggerBake\Lib\Swagger
     */
    private $swagger;

    /**
     * @var \SwaggerBake\Lib\Configuration
     */
    private $config;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Operation
     */
    private $operation;

    /**
     * @var \phpDocumentor\Reflection\DocBlock
     */
    private $doc;

    /**
     * @var array
     */
    private $annotations;

    /**
     * @var \SwaggerBake\Lib\Route\RouteDecorator
     */
    private $route;

    /**
     * @var \SwaggerBake\Lib\OpenApi\Schema|null
     */
    private $schema;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger Swagger
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \phpDocumentor\Reflection\DocBlock $doc DocBlock
     * @param array $annotations An array of annotation objects
     * @param \SwaggerBake\Lib\Route\RouteDecorator $route RouteDecorator
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema Schema
     */
    public function __construct(
        Swagger $swagger,
        Configuration $config,
        Operation $operation,
        DocBlock $doc,
        array $annotations,
        RouteDecorator $route,
        ?Schema $schema
    ) {
        $this->swagger = $swagger;
        $this->config = $config;
        $this->operation = $operation;
        $this->doc = $doc;
        $this->annotations = $annotations;
        $this->route = $route;
        $this->schema = $schema;
    }

    /**
     * Gets an Operation with Responses
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperationWithResponses(): Operation
    {
        $this->assignAnnotations();
        $this->assignDocBlockExceptions();
        $this->assignSchema();
        $this->assignDefaultResponses();

        return $this->operation;
    }

    /**
     * Set Responses using SwagResponseSchema
     *
     * @return void
     */
    private function assignAnnotations(): void
    {
        $swagResponses = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagResponseSchema;
        });

        foreach ($swagResponses as $annotate) {
            $mimeTypes = $annotate->mimeTypes;
            if (empty($mimeTypes)) {
                $mimeTypes = $this->config->getResponseContentTypes();
            }

            foreach ($mimeTypes as $mimeType) {
                $content = (new Content())->setMimeType($mimeType);

                $response = (new Response())->setCode($annotate->httpCode)->setDescription($annotate->description);

                if (empty($annotate->schemaFormat) && empty($annotate->schemaItems) && empty($annotate->refEntity)) {
                    $response->pushContent($content);
                    $this->operation->pushResponse($response);
                    continue;
                }

                $schema = $this->buildSchemaFromAnnotationAndMimeType($annotate, $mimeType);

                $content->setSchema($schema);

                $response->pushContent($content);

                $this->operation->pushResponse($response);
            }
        }
    }

    /**
     * Sets error Responses using throw tags from Dock Block
     *
     * @return void
     */
    private function assignDocBlockExceptions(): void
    {
        if (!$this->doc->hasTag('throws')) {
            return;
        }

        $throws = array_filter($this->doc->getTagsByName('throws'), function ($tag) {
            return $tag instanceof DocBlock\Tags\Throws;
        });

        foreach ($throws as $throw) {
            $exception = new ExceptionHandler($throw);

            $response = (new Response())->setCode($exception->getCode())->setDescription($exception->getMessage());

            foreach ($this->config->getResponseContentTypes() as $mimeType) {
                $response->pushContent(
                    (new Content())
                        ->setMimeType($mimeType)
                        ->setSchema('#/components/schemas/' . $this->config->getExceptionSchema())
                );
            }

            $this->operation->pushResponse($response);
        }
    }

    /**
     * Assigns Cake Models as Swagger Schema if possible.
     *
     * @return void
     */
    private function assignSchema(): void
    {
        $action = strtolower($this->route->getAction());
        $crudActions = ['index','add','view','edit'];

        if (!$this->schema || $this->operation->hasSuccessResponseCode() || !in_array($action, $crudActions)) {
            return;
        }

        $response = (new Response())->setCode('200');

        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $schema = $this->getMimeTypeSchema($mimeType, $action);
            $response->pushContent(
                (new Content())
                    ->setSchema($schema)
                    ->setMimeType($mimeType)
            );
        }

        $this->operation->pushResponse($response);
    }

    /**
     * Gets a schema based on mimetype
     *
     * @param string $mimeType a mime type (e.g. application/xml, application/json)
     * @param string $action controller action (e.g. add, index, view, edit, delete)
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function getMimeTypeSchema(string $mimeType, string $action)
    {
        $schema = $this->schema instanceof Schema ? $this->schema : new Schema();

        switch ($mimeType) {
            case 'application/xml':
                return (new XmlMedia($schema, $this->swagger))->buildSchema($action);
            case 'application/hal+json':
            case 'application/vnd.hal+json':
                return (new HalJson($schema))->buildSchema($action);
            case 'application/ld+json':
                return (new JsonLd($schema))->buildSchema($action);
            case 'text/plain':
                return (new Schema())->setType('string');
        }

        return (new Generic($schema, $this->swagger))->buildSchema($action);
    }

    /**
     * Assigns a default responses
     *
     * delete: 204 with empty response body
     * default: 200 with empty response body and first element from responseContentTypes config as mimeType
     *
     * @return void
     */
    private function assignDefaultResponses(): void
    {
        if ($this->operation->hasSuccessResponseCode()) {
            return;
        }

        if (strtolower($this->route->getAction()) == 'delete') {
            $this->operation->pushResponse(
                (new Response())
                    ->setCode('204')
                    ->setDescription('Resource deleted')
            );

            return;
        }

        $response = (new Response())->setCode('200');

        if (in_array($this->operation->getHttpMethod(), ['OPTIONS','HEAD'])) {
            $this->operation->pushResponse($response);

            return;
        }

        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $schema = (new Schema())->setDescription('');

            if ($mimeType == 'application/xml') {
                $schema->setXml((new Xml())->setName('response'));
            }

            $response->pushContent(
                (new Content())->setMimeType($mimeType)->setSchema($schema)
            );
        }

        $this->operation->pushResponse($response);
    }

    /**
     * Builds a Schema instance from SwagResponseSchema annotation and mime type
     *
     * @param \SwaggerBake\Lib\Annotation\SwagResponseSchema $annotation SwagResponseSchema
     * @param string $mimeType mine type string (i.e application/json)
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function buildSchemaFromAnnotationAndMimeType(SwagResponseSchema $annotation, string $mimeType): Schema
    {
        $schema = new Schema();

        if (!empty($annotation->refEntity)) {
            $schema->setType('object')->setRefEntity($annotation->refEntity);
        } elseif (!empty($annotation->schemaItems)) {
            $schema->setType('array')->setItems($annotation->schemaItems);
        }

        if (!empty($annotation->schemaType)) {
            $schema->setFormat($annotation->schemaType);
        }

        if (empty($schema->getType()) && $mimeType == 'text/plain') {
            $schema->setType('string');
        }

        return $schema->setFormat($annotation->schemaFormat);
    }
}
