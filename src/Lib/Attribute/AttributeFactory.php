<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Reflector;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

final class AttributeFactory
{
    /**
     * @param \Reflector $reflection The reflection
     * @param string $attributeClass Your Attribute class
     */
    public function __construct(
        private Reflector $reflection,
        private string $attributeClass
    ) {
    }

    /**
     * Creates an instance of the attribute class, returns null if no attribute was found
     *
     * @return object|null
     * @throws \ReflectionException
     */
    public function createOneOrNull(): ?object
    {
        if (!method_exists($this->reflection, 'getAttributes')) {
            throw new SwaggerBakeRunTimeException('Reflected instance does not have getAttributes method');
        }

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
        if (!method_exists($this->reflection, 'getAttributes')) {
            throw new SwaggerBakeRunTimeException('Reflected instance does not have getAttributes method');
        }

        $attributes = $this->reflection->getAttributes($this->attributeClass);

        foreach ($attributes as $attr) {
            $array[] = $attr->newInstance();
        }

        return $array ?? [];
    }
}
