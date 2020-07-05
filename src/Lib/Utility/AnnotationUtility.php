<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;

/**
 * Class AnnotationUtility
 *
 * @package SwaggerBake\Lib\Utility
 */
class AnnotationUtility
{
    /**
     * Gets class annotations from namespace argument
     *
     * @uses AnnotationReader
     * @uses ReflectionClass
     * @param string $namespace Fully qualified namespace of the class
     * @return array
     */
    public static function getClassAnnotationsFromFqns(string $namespace): array
    {
        try {
            $instance = new $namespace();
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
     * Gets class annotations from instance
     *
     * @uses AnnotationReader
     * @uses ReflectionClass
     * @param object $instance PHP object
     * @return array
     */
    public static function getClassAnnotationsFromInstance(object $instance): array
    {
        try {
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
     * @param string $namespace Fully qualified namespace
     * @param string $method Method name
     * @return array
     */
    public static function getMethodAnnotations(string $namespace, string $method): array
    {
        $return = [];

        try {
            $instance = new $namespace();
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
