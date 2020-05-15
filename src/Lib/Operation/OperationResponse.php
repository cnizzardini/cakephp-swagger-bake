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

        foreach ($swagResponses as $annotation) {
            $response = (new Response())
                ->setCode(intval($annotation->httpCode))
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

        foreach ($throws as $throw) {
            $exception = new ExceptionHandler($throw->getType()->__toString());
            $this->operation->pushResponse(
                (new Response())->setCode($exception->getCode())->setDescription($exception->getMessage())
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

        if ($this->operation->getResponseByCode(200)) {
            return;
        }

        if (!in_array(strtolower($this->route->getAction()),['index','add','view','edit'])) {
            return;
        }

        if (in_array(strtolower($this->route->getAction()),['index'])) {
            $response = (new Response())->setCode(200);

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
            $response = (new Response())->setCode(200);

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
}