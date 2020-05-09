<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\RequestBody;

class SwagRequestBodyHandler
{
    /**
     * @param SwagRequestBody $annotation
     * @return RequestBody
     */
    public function getResponse(SwagRequestBody $annotation) : RequestBody
    {
        return (new RequestBody())
            ->setDescription($annotation->description)
            ->setRequired((bool) $annotation->required)
            ->setIgnoreCakeSchema((bool) $annotation)
        ;
    }
}