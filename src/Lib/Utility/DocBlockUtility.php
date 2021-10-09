<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Class DocBlockUtility
 *
 * @package SwaggerBake\Lib\Utility
 */
class DocBlockUtility
{
    /**
     * Returns docblock for instance->methodName supplied
     *
     * @param object $instance Object
     * @param string $methodName Method name
     * @return \phpDocumentor\Reflection\DocBlock|null
     */
    public static function getMethodDocBlock(object $instance, string $methodName): ?DocBlock
    {
        try {
            $reflectionMethod = new ReflectionMethod(get_class($instance), $methodName);
        } catch (\ReflectionException $e) {
            return null;
        }

        $comment = $reflectionMethod->getDocComment();

        if (!$comment) {
            return null;
        }

        $docFactory = DocBlockFactory::createInstance();

        return $docFactory->create($comment);
    }

    /**
     * Returns key-value array of property name and ReflectedProperty
     *
     * @param object $instance Object
     * @return \ReflectionProperty[]
     */
    public static function getProperties(object $instance): ?array
    {
        try {
            $reflectionClass = new ReflectionClass(get_class($instance));
            $reflectedProperties = $reflectionClass->getProperties();
        } catch (\ReflectionException $e) {
            return null;
        }

        if (empty($reflectedProperties)) {
            return null;
        }

        $return = [];
        foreach ($reflectedProperties as $property) {
            $return[$property->name] = $property;
        }

        return $return ?? null;
    }

    /**
     * @param \ReflectionProperty $property ReflectionProperty
     * @return \phpDocumentor\Reflection\DocBlock
     */
    public static function getPropertyDocBlock(ReflectionProperty $property): DocBlock
    {
        print_r($property);
        $comment = $property->getDocComment();
        $docFactory = DocBlockFactory::createInstance();

        return $docFactory->create($comment);
    }

    /**
     * Returns string representation of Var_ data type. Can be either string, integer, boolean, or null if unknown
     *
     * @param \phpDocumentor\Reflection\DocBlock\Tags\Var_ $var DocBlock\Tags\Var_
     * @return string|null
     */
    public static function getDocBlockConvertedVar(Var_ $var): ?string
    {
        if ($var->getType() instanceof String_) {
            return 'string';
        }
        if ($var->getType() instanceof Integer) {
            return 'integer';
        }
        if ($var->getType() instanceof Boolean) {
            return 'boolean';
        }

        return null;
    }
}
