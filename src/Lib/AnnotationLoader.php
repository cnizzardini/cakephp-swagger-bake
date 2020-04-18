<?php

namespace SwaggerBake\Lib;

use Doctrine\Common\Annotations\AnnotationRegistry;
use SwaggerBake\Lib\Annotation as SwagAnnotation;

class AnnotationLoader
{
    public static function load()
    {
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagEntity::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagEntityAttribute::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagForm::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagHeader::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagPaginator::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagPath::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagQuery::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagRequestBody::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagRequestBodyContent::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagResponseSchema::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagSecurity::class);
    }
}