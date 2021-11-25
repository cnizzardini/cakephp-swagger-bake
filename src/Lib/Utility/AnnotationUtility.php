<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;

/**
 * @deprecated This class is marked for removal in v2.1.0
 * @codeCoverageIgnore
 */
class AnnotationUtility
{
    /**
     * @var string[]
     */
    private const DEPRECATIONS = [
        'SwaggerBake\Lib\Annotation\SwagDto' => 'SwaggerBake\Lib\Attribute\OpenApiDto',
        'SwaggerBake\Lib\Annotation\SwagDtoQuery' => 'SwaggerBake\Lib\Attribute\OpenApiDtoQuery',
        'SwaggerBake\Lib\Annotation\SwagDtoRequestBody' => 'SwaggerBake\Lib\Attribute\OpenApiDtoQuery',
        'SwaggerBake\Lib\Annotation\SwagDtoRequestRequestBody' => 'SwaggerBake\Lib\Attribute\OpenApiDtoRequestBody',
        'SwaggerBake\Lib\Annotation\SwagEntity' => 'SwaggerBake\Lib\Attribute\OpenApiSchema',
        'SwaggerBake\Lib\Annotation\SwagEntityAttribute' => 'SwaggerBake\Lib\Attribute\OpenApiSchemaProperty',
        'SwaggerBake\Lib\Annotation\SwagForm' => 'SwaggerBake\Lib\Attribute\OpenApiForm',
        'SwaggerBake\Lib\Annotation\SwagHeader' => 'SwaggerBake\Lib\Attribute\OpenApiHeader',
        'SwaggerBake\Lib\Annotation\SwagQuery' => 'SwaggerBake\Lib\Attribute\OpenApiQuery',
        'SwaggerBake\Lib\Annotation\SwagOperation' => 'SwaggerBake\Lib\Attribute\OpenApiOperation',
        'SwaggerBake\Lib\Annotation\SwagPaginator' => 'SwaggerBake\Lib\Attribute\OpenApiPaginator',
        'SwaggerBake\Lib\Annotation\SwagPath' => 'SwaggerBake\Lib\Attribute\OpenApiPath',
        'SwaggerBake\Lib\Annotation\SwagPathParameter' => 'SwaggerBake\Lib\Attribute\OpenApiPathParameter',
        'SwaggerBake\Lib\Annotation\SwagRequestBody' => 'SwaggerBake\Lib\Attribute\OpenApiRequestBody',
        'SwaggerBake\Lib\Annotation\SwagRequestBodyContent' => 'SwaggerBake\Lib\Attribute\OpenApiRequestBody',
        'SwaggerBake\Lib\Annotation\SwagResponseSchema' => 'SwaggerBake\Lib\Attribute\OpenApiResponse',
        'SwaggerBake\Lib\Annotation\SwagSecurity' => 'SwaggerBake\Lib\Attribute\OpenApiSecurity',
    ];

    /**
     * Gets class annotations from namespace argument
     *
     * @uses AnnotationReader
     * @uses ReflectionClass
     * @param string $namespace Fully qualified namespace of the class
     * @return void
     */
    public static function checkClassAnnotations(string $namespace): void
    {
        try {
            $instance = new $namespace();
            $reflectionClass = new ReflectionClass(get_class($instance));
        } catch (Exception $e) {
            return;
        }

        $reader = new AnnotationReader();

        $annotations = $reader->getClassAnnotations($reflectionClass);

        if (is_array($annotations) && count($annotations)) {
            foreach ($annotations as $annotation) {
                self::warning(get_class($annotation), $namespace);
            }
        }
    }

    /**
     * Gets class annotations from instance
     *
     * @uses AnnotationReader
     * @uses ReflectionClass
     * @param object $instance PHP object
     * @return void
     */
    public static function checkClassAnnotationsFromInstance(object $instance): void
    {
        try {
            $reflectionClass = new ReflectionClass(get_class($instance));
        } catch (Exception $e) {
            return;
        }

        $reader = new AnnotationReader();

        $annotations = $reader->getClassAnnotations($reflectionClass);

        if (is_array($annotations) && count($annotations)) {
            foreach ($annotations as $annotation) {
                self::warning(get_class($annotation), get_class($instance));
            }
        }
    }

    /**
     * Returns an array of Lib/Annotation objects that can be applied to methods
     *
     * @uses AnnotationReader
     * @uses ReflectionClass
     * @param string $namespace Fully qualified namespace
     * @param string $method Method name
     * @return void
     */
    public static function checkMethodAnnotations(string $namespace, string $method): void
    {
        try {
            $instance = new $namespace();
            $reflectionClass = new ReflectionClass(get_class($instance));
            $reflectedMethods = $reflectionClass->getMethods();
        } catch (Exception $e) {
            return;
        }

        $argMethodAnnotations = array_filter($reflectedMethods, function ($refMethod) use ($method) {
            return $refMethod->name == $method;
        });

        $reader = new AnnotationReader();

        foreach ($argMethodAnnotations as $methodAnnotation) {
            $annotations = $reader->getMethodAnnotations($methodAnnotation);
            if (!empty($annotations)) {
                foreach ($annotations as $annotation) {
                    self::warning(get_class($annotation), $namespace, $method);
                }
            }
        }
    }

    /**
     * Issues a deprecation message
     *
     * @param string $annotation The annotation classes FQN
     * @param string $class The FQN of the class where the annotation is defined
     * @param string|null $method The FQN of the class where the annotation is defined
     * @return void
     */
    private static function warning(string $annotation, string $class, ?string $method = null): void
    {
        if (isset(static::DEPRECATIONS[$annotation])) {
            triggerWarning(
                sprintf(
                    'Replace %s with %s in %s',
                    $annotation,
                    static::DEPRECATIONS[$annotation],
                    $class . ($method !== null ? ":$method" : '')
                )
            );
        }
    }
}
