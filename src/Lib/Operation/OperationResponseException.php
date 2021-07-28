<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\Swagger;

class OperationResponseException
{
    private Swagger $swagger;

    private Configuration $config;

    private Operation $operation;

    private DocBlock $doc;

    /**
     * @param \SwaggerBake\Lib\Swagger $swagger an instance of Swagger
     * @param \SwaggerBake\Lib\Configuration $config an instance of Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation an instance of Operation
     * @param \phpDocumentor\Reflection\DocBlock $doc an instance of DocBlock
     */
    public function __construct(Swagger $swagger, Configuration $config, Operation $operation, DocBlock $doc)
    {
        $this->swagger = $swagger;
        $this->config = $config;
        $this->operation = $operation;
        $this->doc = $doc;
    }

    /**
     * Returns the Operation after setting error responses using throw tags from a controllers doc block's
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperation(): Operation
    {
        if (!$this->doc->hasTag('throws')) {
            return $this->operation;
        }

        $throws = array_filter($this->doc->getTagsByName('throws'), function ($tag) {
            return $tag instanceof DocBlock\Tags\Throws;
        });

        foreach ($throws as $throw) {
            $exception = new ExceptionHandler($throw, $this->swagger, $this->config);

            $response = (new Response())
                ->setCode($exception->getCode())
                ->setDescription($exception->getMessage());

            foreach ($this->config->getResponseContentTypes() as $mimeType) {
                $response->pushContent(
                    (new Content())
                        ->setMimeType($mimeType)
                        ->setSchema($exception->getSchema())
                );
            }

            $this->operation->pushResponse($response);
        }

        return $this->operation;
    }
}
