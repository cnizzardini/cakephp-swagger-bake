<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;
use SwaggerBake\Lib\OpenApi\Xml;

/**
 * Class OperationResponse
 *
 * @package SwaggerBake\Lib\Operation
 */
class OperationResponse
{
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
     * @param \SwaggerBake\Lib\Configuration $config Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation Operation
     * @param \phpDocumentor\Reflection\DocBlock $doc DocBlock
     * @param array $annotations An array of annotation objects
     * @param \SwaggerBake\Lib\Decorator\RouteDecorator $route RouteDecorator
     * @param \SwaggerBake\Lib\OpenApi\Schema|null $schema Schema
     */
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

        $mimeTypes = $this->config->getResponseContentTypes();
        $defaultMimeType = reset($mimeTypes);

        foreach ($swagResponses as $annotation) {
            $content = (new Content())->setMimeType($annotation->mimeType);

            if (empty($content->getMimeType())) {
                $content->setMimeType($defaultMimeType);
            }

            $response = (new Response())->setCode($annotation->httpCode)->setDescription($annotation->description);

            if (empty($annotation->schemaFormat) && empty($annotation->schemaItems) && empty($annotation->refEntity)) {
                $response->pushContent($content);
                $this->operation->pushResponse($response);
                continue;
            }

            $schema = $this->buildSchemaFromAnnotation($annotation);

            $content->setSchema($schema);

            $response->pushContent($content);

            $this->operation->pushResponse($response);
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
     * Assigns Cake Models as Swagger Schema if possible. For index actions, an array of objects will be assigned.
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

        $schema = clone $this->schema;

        if ($action === 'index') {
            $schema = (new Schema())
                ->setType('array')
                ->setItems(['$ref' => '#/components/schemas/' . $this->schema->getName()]);
        }

        $response = (new Response())->setCode('200');

        foreach ($this->config->getResponseContentTypes() as $mimeType) {
            $schema->setXml(null);

            if ($mimeType == 'application/xml') {
                $schema->setXml((new Xml())->setName('response'));
            }

            $response->pushContent(
                (new Content())
                    ->setSchema($schema)
                    ->setMimeType($mimeType)
            );
        }

        $this->operation->pushResponse($response);
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
     * Builds a Schema instance from SwagResponseSchema annotation
     *
     * @param \SwaggerBake\Lib\Annotation\SwagResponseSchema $annotation SwagResponseSchema
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    private function buildSchemaFromAnnotation(SwagResponseSchema $annotation): Schema
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

        if (empty($schema->getType()) && $annotation->mimeType == 'text/plain') {
            $schema->setType('string');
        }

        return $schema->setFormat($annotation->schemaFormat);
    }
}
