<?php

namespace SwaggerBake\Lib;

use Doctrine\Common\Annotations\AnnotationRegistry;
use SwaggerBake\Lib\Annotation as SwagAnnotation;

class AnnotationLoader
{
    public static function load()
    {
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagHeader::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagPaginator::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagQuery::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagForm::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagSecurity::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagEntityAttribute::class);
    }
}