<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\Response;

class SwagResponseSchemaHandler
{
    public function getResponse(SwagResponseSchema $annotation) : Response
    {
        return (new Response())
            ->setSchemaRef($annotation->refEntity)
            ->setCode(intval($annotation->httpCode))
            ->setDescription($annotation->description);
    }
}