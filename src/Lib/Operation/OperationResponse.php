<?php


namespace SwaggerBake\Lib\Operation;

use Cake\Utility\Inflector;
use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\Model\ExpressiveRoute;
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

    /** @var ExpressiveRoute  */
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
        ExpressiveRoute $route,
        ?Schema $schema
    ) {
        $this->config = $config;
        $this->operation = $operation;
        $this->doc = $doc;
        $this->annotations = $annotations;
        $this->route = $route;
        $this->schema = $schema;
    }

    public function getOperationWithResponses() : Operation
    {
        $this->withAnnotations();
        $this->withDocBlockExceptions();
        $this->withSchema();

        return $this->operation;
    }

    private function withAnnotations() : void
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

    private function withDocBlockExceptions() : void
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

    private function withSchema() : void
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