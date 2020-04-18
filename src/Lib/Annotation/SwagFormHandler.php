<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\SchemaProperty;

class SwagFormHandler
{
    public function getSchemaProperty(SwagForm $annotation) : SchemaProperty
    {
        $schemaProperty = new SchemaProperty();
        $schemaProperty
            ->setName($annotation->name)
            ->setType($annotation->type)
            ->setRequired($annotation->required)
        ;

        return $schemaProperty;
    }
}