<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\SchemaProperty;

class SwagEntityAttributeHandler
{
    /**
     * @param SwagEntityAttribute $annotation
     * @return SchemaProperty
     */
    public function getSchemaProperty(SwagEntityAttribute $annotation) : SchemaProperty
    {
        return (new SchemaProperty())
            ->setName($annotation->name)
            ->setDescription($annotation->description)
            ->setType($annotation->type)
            ->setReadOnly($annotation->readOnly)
            ->setWriteOnly($annotation->writeOnly)
            ->setRequired($annotation->required)
        ;
    }
}