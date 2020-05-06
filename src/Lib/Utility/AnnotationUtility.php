<?php

namespace SwaggerBake\Lib\Utility;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;

class AnnotationUtility
{
    /**
     * Gets class annotations from full namespace argument
     *
     * @uses AnnotationReader
     * @uses ReflectionClass
     * @param string $namespace
     * @return array
     */
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

    /**
     * Returns an array of Lib/Annotation objects that can be applied to methods
     *
     * @uses AnnotationReader
     * @uses ReflectionClass
     * @param string $namespace
     * @param string $method
     * @return array
     */
    public static function getMethodAnnotations(string $namespace, string $method) : array
    {
        $return = [];

        try {
            $instance = new $namespace;
            $reflectionClass = new ReflectionClass(get_class($instance));
            $reflectedMethods = $reflectionClass->getMethods();
        } catch (Exception $e) {
            return $return;
        }

        $argMethodAnnotations = array_filter($reflectedMethods, function ($refMethod) use ($method) {
            return $refMethod->name == $method;
        });

        $reader = new AnnotationReader();

        foreach ($argMethodAnnotations as $methodAnnotation) {
            $annotations = $reader->getMethodAnnotations($methodAnnotation);
            if (empty($annotations)) {
                continue;
            }
            $return = array_merge($return, $annotations);
        }

        return $return;
    }
}