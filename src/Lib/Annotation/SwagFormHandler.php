<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\SchemaProperty;

class SwagFormHandler
{
    /**
     * @param SwagForm $annotation
     * @return SchemaProperty
     */
    public function getSchemaProperty(SwagForm $annotation) : SchemaProperty
    {
        return (new SchemaProperty())
            ->setDescription($annotation->description)
            ->setName($annotation->name)
            ->setType($annotation->type)
            ->setRequired($annotation->required)
        ;
    }
}