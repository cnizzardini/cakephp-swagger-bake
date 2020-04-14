<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

class SwagPostHandler
{
    public function getPostParameter(SwagPost $annotation) : Parameter
    {
        $parameter = new Parameter();
        $parameter
            ->setName($annotation->name)
            ->setAllowEmptyValue(false)
            ->setDeprecated(false)
            ->setRequired($annotation->required)
            ->setIn('body')
            ->setSchema((new Schema())->setType($annotation->type));

        return $parameter;
    }
}