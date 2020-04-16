<?php

namespace SwaggerBake\Lib\Utility;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;

class AnnotationUtility
{
    public static function getClassAnnotations(string $namespace) : array
    {
        try {
            $instance = new $namespace;
            $reflectionClass = new ReflectionClass(get_class($instance));
        } catch (Exception $e) {
            return [];
        }

        $reader = new AnnotationReader();

        $annotations = $reader->getClassAnnotations($reflectionClass);

        if (!is_array($annotations)) {
            return [];
        }

        return $annotations;
    }
}