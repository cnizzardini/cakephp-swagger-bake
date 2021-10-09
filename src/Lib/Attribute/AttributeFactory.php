<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

final class AttributeFactory
{
    private ReflectionClass|ReflectionClassConstant|ReflectionFunction|ReflectionMethod|ReflectionParameter|
    ReflectionProperty $reflection;

    private string $attributeClass;

    /**
     * @param \ReflectionClass|\ReflectionClassConstant|\ReflectionFunction|\ReflectionMethod|\ReflectionParameter|\ReflectionProperty $reflection The reflection
     * @param string $attributeClass Your Attribute class
     */
    public function __construct(
        ReflectionClass|ReflectionClassConstant|ReflectionFunction|ReflectionMethod|ReflectionParameter|
        ReflectionProperty $reflection,
        string $attributeClass
    ) {
        $this->reflection = $reflection;
        $this->attributeClass = $attributeClass;
    }

    /**
     * Creates an instance of the attribute class, returns null if no attribute was found
     *
     * @return object|null
     * @throws \ReflectionException
     */
    public function createOneOrNull(): ?object
    {
        $attributes = $this->reflection->getAttributes($this->attributeClass);
        if (empty($attributes)) {
            return null;
        }

        /** @var \ReflectionAttribute $attr */
        $attr = reset($attributes);

        return $attr->newInstance();
    }

    /**
     * Creates many instances of the attribute class and returns them in an array. This is useful when an Attribute
     * has the IS_REPEATABLE flag set.
     *
     * @return array
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function createMany(): array
    {
        $attributes = $this->reflection->getAttributes($this->attributeClass);

        foreach ($attributes as $attr) {
            $array[] = $attr->newInstance();
        }

        return $array ?? [];
    }
}
