<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use RuntimeException;

final class AttributeInstance
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
    public function createOne(): ?object
    {
        $attributes = $this->reflection->getAttributes($this->attributeClass);
        if (empty($attributes)) {
            return null;
        }

        $attr = reset($attributes);

        return $this->create($attr->getArguments());
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
        $attributes = (new ReflectionClass($this->attributeClass))->getAttributes(Attribute::class);
        if (empty($attributes)) {
            throw new RuntimeException(
                sprintf(
                    'The Attribute class `%s` is invalid because it has no Attribute declaration',
                    $this->attributeClass
                )
            );
        }

        $attributes = $this->reflection->getAttributes($this->attributeClass);
        if (empty($attributes)) {
            return [];
        }

        $array = [];
        foreach ($attributes as $attr) {
            $args = $attr->getArguments();
            $array[] = $this->create($args);
        }

        return $array;
    }

    /**
     * Creates an instance of the attribute class
     *
     * @param array $args An array of the attribute arguments
     * @return object
     * @throws \ReflectionException
     */
    private function create(array $args): object
    {
        $refAttribute = new ReflectionClass($this->attributeClass);
        $refMethod = $refAttribute->getMethod('__construct');
        $refParameters = $refMethod->getParameters();

        $params = [];
        foreach ($refParameters as $refParam) {
            if (isset($args[$refParam->getName()])) {
                $params[$refParam->getPosition()] = $args[$refParam->getName()];
                continue;
            }

            $params[$refParam->getPosition()] = $refParam->getDefaultValue();
        }

        return $refAttribute->newInstanceArgs($params);
    }
}
