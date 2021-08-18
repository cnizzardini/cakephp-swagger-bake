<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use Doctrine\Common\Annotations\AnnotationRegistry;
use SwaggerBake\Lib\Annotation as SwagAnnotation;

/**
 * Class AnnotationLoader
 *
 * @package SwaggerBake\Lib
 */
class AnnotationLoader
{
    /**
     * Loads SwaggerBake annotations
     *
     * @return void
     */
    public static function load(): void
    {
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagDto::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagDtoRequestBody::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagDtoQuery::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagEntityAttribute::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagForm::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagHeader::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagPaginator::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagQuery::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagRequestBody::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagRequestBodyContent::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagResponseSchema::class);
        AnnotationRegistry::loadAnnotationClass(SwagAnnotation\SwagPathParameter::class);
    }
}
