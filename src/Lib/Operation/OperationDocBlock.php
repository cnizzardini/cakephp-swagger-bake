<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\Response;
use SwaggerBake\Lib\Swagger;

class OperationDocBlock
{
    /**
     * @param \SwaggerBake\Lib\Swagger $swagger an instance of Swagger
     * @param \SwaggerBake\Lib\Configuration $config an instance of Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation an instance of Operation
     * @param \phpDocumentor\Reflection\DocBlock $doc an instance of DocBlock
     */
    public function __construct(
        private Swagger $swagger,
        private Configuration $config,
        private Operation $operation,
        private DocBlock $doc
    ) {
    }

    /**
     * Assigns metadata from the controller method doc blocks
     *
     * @return \SwaggerBake\Lib\OpenApi\Operation
     */
    public function getOperation(): Operation
    {
        $this->comments();
        $this->throws();

        return $this->operation;
    }

    /**
     * Adds PHP Doc Block tags and metadata to the Operation
     *
     * @return void
     */
    private function comments(): void
    {
        $this->operation
            ->setSummary($this->doc->getSummary())
            ->setDescription($this->doc->getDescription()->render());

        if ($this->doc->hasTag('deprecated')) {
            $this->operation->setDeprecated(true);
        }

        if (!$this->doc->hasTag('see')) {
            return;
        }

        $tags = $this->doc->getTagsByName('see');
        $seeTag = reset($tags);
        $str = $seeTag->__toString();
        $pieces = explode(' ', $str);

        if (!filter_var($pieces[0], FILTER_VALIDATE_URL)) {
            return;
        }

        [$url, $description] = $pieces;

        $this->operation->setExternalDocs(new OperationExternalDoc($url, $description));
    }

    /**
     * Sets error responses using throw tags from a controller's doc block's
     *
     * @return void
     */
    public function throws(): void
    {
        if (!$this->doc->hasTag('throws')) {
            return;
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
    }
}
