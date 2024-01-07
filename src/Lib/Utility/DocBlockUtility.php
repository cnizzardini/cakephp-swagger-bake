<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionException;
use ReflectionMethod;

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
        } catch (ReflectionException $e) {
            return null;
        }

        $comment = $reflectionMethod->getDocComment();

        if (!$comment) {
            return null;
        }

        $docFactory = DocBlockFactory::createInstance();

        return $docFactory->create($comment);
    }
}
