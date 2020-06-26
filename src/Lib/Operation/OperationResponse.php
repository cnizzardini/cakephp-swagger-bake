<?php

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Decorator\RouteDecorator;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\OpenApi\Schema;

/**
 * Class OperationResponse
 * @package SwaggerBake\Lib\Operation
 */
class OperationResponse
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
     * Gets an Operation with Responses
     * @return Operation
     */
    public function getOperationWithResponses() : Operation
    {
        $this->assignAnnotations();
        $this->assignDocBlockExceptions();
        $this->assignSchema();
        $this->assignDefaultResponses();

        return $this->operation;
    }

    /**
     * Set Responses using SwagResponseSchema
     * @return void
     */
    private function assignAnnotations() : void
    {
        $swagResponses = array_filter($this->annotations, function ($annotation) {
            return $annotation instanceof SwagResponseSchema;
        });

        $mimeTypes = $this->config->getResponseContentTypes();
        $defaultMimeType = reset($mimeTypes);

        foreach ($swagResponses as $annotation) {

            if (empty($annotation->mimeType) && !empty($annotation->refEntity)) {
                $annotation->mimeType = $defaultMimeType;
            }

            $response = (new Response())
                ->setCode($annotation->httpCode)
                ->setDescription($annotation->description);

            if (empty($annotation->schemaFormat) && empty($annotation->mimeType)) {
                $this->operation->pushResponse($response);
                continue;
            }

            $response->pushContent(
                (new Content())
                    ->setSchema($annotation->refEntity)
                    ->setFormat($annotation->schemaFormat)
                    ->setType($annotation->schemaType)
                    ->setMimeType($annotation->mimeType)
            );
            $this->operation->pushResponse($response);
        }
    }

    /**
     * Sets error Responses using throw tags from Dock Block
     * @return void
     */
    private function assignDocBlockExceptions() : void
    {
        if (!$this->doc->hasTag('throws')) {
            return;
        }

        $throws = $this->doc->getTagsByName('throws');

        $mimeTypes = $this->config->getResponseContentTypes();
        $mimeType = reset($mimeTypes);

        foreach ($throws as $throw) {
            $exception = new ExceptionHandler($throw);

            $this->operation->pushResponse(
                (new Response())
                    ->setCode($exception->getCode())
                    ->setDescription($exception->getMessage())
                    ->pushContent(
                        (new Content())
                            ->setMimeType($mimeType)
                            ->setSchema('#/components/schemas/' . $this->config->getExceptionSchema())
                    )
            );
        }
    }

    /**
     * Assigns Cake Models as Swagger Schema if possible
     * @return void
     */
    private function assignSchema() : void
    {
        if (!$this->schema) {
            return;
        }

        if ($this->operation->hasSuccessResponseCode()) {
            return;
        }

        if (!in_array(strtolower($this->route->getAction()),['index','add','view','edit'])) {
            return;
        }

        if (in_array(strtolower($this->route->getAction()),['index'])) {
            $response = (new Response())->setCode('200');

            foreach ($this->config->getResponseContentTypes() as $mimeType) {
                $response->pushContent(
                    (new Content())
                        ->setSchema($this->schema)
                        ->setMimeType($mimeType)
                );
            }
            $this->operation->pushResponse($response);
            return;
        }

        if (in_array(strtolower($this->route->getAction()),['add','view','edit'])) {
            $response = (new Response())->setCode('200');

            foreach ($this->config->getResponseContentTypes() as $mimeType) {
                $response->pushContent(
                    (new Content())
                        ->setSchema($this->schema)
                        ->setMimeType($mimeType)
                );
            }
            $this->operation->pushResponse($response);
            return;
        }
    }

    /**
     * Assigns a default responses
     *
     * delete: 204 with empty response body
     * default: 200 with empty response body and first element from responseContentTypes config as mimeType
     *
     * @response void
     */
    private function assignDefaultResponses() : void
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

        $types = $this->config->getResponseContentTypes();

        $this->operation->pushResponse(
            (new Response())
                ->setCode('200')
                ->pushContent(
                    (new Content())
                        ->setMimeType(reset($types))
                        ->setSchema(new Schema())
                )
        );

        return;
    }
}