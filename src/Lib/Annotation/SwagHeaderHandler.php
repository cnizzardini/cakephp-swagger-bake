<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

class SwagHeaderHandler
{
    public function getHeaderParameters(SwagHeader $annotation) : Parameter
    {
        $parameter = new Parameter();
        $parameter
            ->setName($annotation->name)
            ->setAllowEmptyValue(false)
            ->setDeprecated(false)
            ->setRequired($annotation->required)
            ->setIn('header')
            ->setSchema((new Schema())->setType($annotation->type));

        return $parameter;
    }
}