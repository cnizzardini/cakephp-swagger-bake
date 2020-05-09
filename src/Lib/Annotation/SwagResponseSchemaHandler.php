<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\Content;
use SwaggerBake\Lib\OpenApi\Response;

class SwagResponseSchemaHandler
{
    /**
     * @param SwagResponseSchema $annotation
     * @return Response
     */
    public function getResponse(SwagResponseSchema $annotation) : Response
    {
        $response = (new Response())
            ->setCode(intval($annotation->httpCode))
            ->setDescription($annotation->description);

        if (empty($annotation->schemaFormat) && empty($annotation->mimeType)) {
            return $response;
        }

        return $response->pushContent(
            (new Content())
                ->setSchema($annotation->refEntity)
                ->setFormat($annotation->schemaFormat)
                ->setType($annotation->schemaType)
                ->setMimeType($annotation->mimeType)
        );
    }
}