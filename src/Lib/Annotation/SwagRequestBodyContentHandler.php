<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\Content;

class SwagRequestBodyContentHandler
{
    public function getContent(SwagRequestBodyContent $annotation) : ?Content
    {
        return (new Content())
            ->setMimeType($annotation->mimeType)
            ->setSchema($annotation->refEntity)
        ;
    }
}