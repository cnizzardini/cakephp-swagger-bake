<?php


namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\Annotation\SwagResponseSchema;
use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\Response;

class OperationResponse
{
    public function getOperationWithResponses(Operation $operation, DocBlock $doc, array $annotations) : Operation
    {
        $operation = $this->withAnnotations($operation, $annotations);
        $operation = $this->withDocBlockExceptions($operation, $doc);
        return $operation;
    }

    /**
     * @param Operation $operation
     * @param array $annotations
     * @return Operation
     */
    private function withAnnotations(Operation $operation, array $annotations) : Operation
    {
        $swagResponses = array_filter($annotations, function ($annotation) {
            return $annotation instanceof SwagResponseSchema;
        });

        foreach ($swagResponses as $annotation) {
            $response = (new Response())
                ->setCode(intval($annotation->httpCode))
                ->setDescription($annotation->description);

            if (empty($annotation->schemaFormat) && empty($annotation->mimeType)) {
                $operation->pushResponse($response);
                continue;
            }

            $response->pushContent(
                (new Content())
                    ->setSchema($annotation->refEntity)
                    ->setFormat($annotation->schemaFormat)
                    ->setType($annotation->schemaType)
                    ->setMimeType($annotation->mimeType)
            );
            $operation->pushResponse($response);
        }

        return $operation;
    }

    /**
     * @param Operation $operation
     * @param DocBlock $doc
     * @return Operation
     */
    private function withDocBlockExceptions(Operation $operation, DocBlock $doc) : Operation
    {
        if (!$doc->hasTag('throws')) {
            return $operation;
        }

        $throws = $doc->getTagsByName('throws');

        foreach ($throws as $throw) {
            $exception = new ExceptionHandler($throw->getType()->__toString());
            $operation->pushResponse(
                (new Response())->setCode($exception->getCode())->setDescription($exception->getMessage())
            );
        }

        return $operation;
    }
}