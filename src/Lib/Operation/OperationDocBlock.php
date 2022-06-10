<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag;
use SwaggerBake\Lib\Configuration;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;
use SwaggerBake\Lib\OpenApi\Response;

/**
 * Adds data from Doc Blocks to the Operation.
 */
class OperationDocBlock
{
    /**
     * @param \SwaggerBake\Lib\Configuration $config an instance of Configuration
     * @param \SwaggerBake\Lib\OpenApi\Operation $operation an instance of Operation
     * @param \phpDocumentor\Reflection\DocBlock $doc an instance of DocBlock
     */
    public function __construct(
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
        if ($this->operation->getSummary() == null && !empty($this->doc->getSummary())) {
            $this->operation->setSummary($this->doc->getSummary());
        }
        if ($this->operation->getDescription() == null && !empty($this->doc->getDescription()->render())) {
            $this->operation->setDescription($this->doc->getDescription()->render());
        }

        if ($this->doc->hasTag('deprecated')) {
            $this->operation->setDeprecated(true);
        }

        if (!$this->doc->hasTag('see')) {
            return;
        }

        $this->addExternalDocumentation();
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
            $exception = (new ExceptionResponse($this->config))->build($throw);
            $response = new Response($exception->getCode(), $exception->getDescription());
            $schema = $exception->getSchema();
            if ($schema != null) {
                foreach ($this->config->getResponseContentTypes() as $mimeType) {
                    $response->pushContent(new Content($mimeType, $exception->getSchema()));
                }
            }

            $this->operation->pushResponse($response);
        }
    }

    /**
     * Add external documentation from `@link` first, then `@see` second if either has a valid URL.
     *
     * @return void
     */
    private function addExternalDocumentation(): void
    {
        if ($this->doc->hasTag('link')) {
            $tags = $this->doc->getTagsByName('link');
            $tag = reset($tags);
            if ($tag instanceof Tag) {
                $operationExternalDoc = $this->getOperationExternalDoc($tag);
                if ($operationExternalDoc) {
                    $this->operation->setExternalDocs($operationExternalDoc);

                    return;
                }
            }
        }

        if ($this->doc->hasTag('see')) {
            $tags = $this->doc->getTagsByName('see');
            $tag = reset($tags);
            if ($tag instanceof Tag) {
                $operationExternalDoc = $this->getOperationExternalDoc($tag);
                if ($operationExternalDoc) {
                    $this->operation->setExternalDocs($operationExternalDoc);
                }
            }
        }
    }

    /**
     * Returns an OperationExternalDoc if the Tag has a valid URL.
     *
     * @param \phpDocumentor\Reflection\DocBlock\Tag $tag The Tag to check for a valid URL.
     * @return \SwaggerBake\Lib\OpenApi\OperationExternalDoc|null
     */
    private function getOperationExternalDoc(Tag $tag): ?OperationExternalDoc
    {
        $str = $tag->__toString();
        $pieces = explode(' ', $str);
        if (isset($pieces[0]) && filter_var($pieces[0], FILTER_VALIDATE_URL)) {
            return new OperationExternalDoc($pieces[0], $pieces[1] ?? '');
        }

        return null;
    }
}
