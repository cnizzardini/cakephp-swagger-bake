<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\Parameter;
use SwaggerBake\Lib\OpenApi\Schema;

class SwagHeaderHandler
{
    /**
     * @param SwagHeader $annotation
     * @return Parameter
     */
    public function getHeaderParameters(SwagHeader $annotation) : Parameter
    {
        return (new Parameter())
            ->setName($annotation->name)
            ->setDescription($annotation->description)
            ->setAllowEmptyValue(false)
            ->setDeprecated(false)
            ->setRequired($annotation->required)
            ->setIn('header')
            ->setSchema((new Schema())->setType($annotation->type))
        ;
    }
}