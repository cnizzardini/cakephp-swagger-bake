<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\SchemaProperty;

class SwagEntityAttributeHandler
{
    public function getSchemaProperty(SwagEntityAttribute $annotation) : SchemaProperty
    {
        $schemaProperty = new SchemaProperty();
        $schemaProperty
            ->setName($annotation->name)
            ->setType($annotation->type)
            ->setReadOnly($annotation->readOnly)
            ->setWriteOnly($annotation->writeOnly)
            ->setRequired($annotation->required)
        ;

        return $schemaProperty;
    }
}