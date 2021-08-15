<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use ReflectionClass;

trait AttributeTrait
{
    /**
     * Creates an instance of the Attribute based on the reflected attributes
     *
     * @param string $class The attribute class, i.e. OpenApiPath::class
     * @param \ReflectionClass $reflection The reflection of the class using the attribute
     * @return self
     */
    public static function createFromReflection(string $class, ReflectionClass $reflection): self
    {
        $instance = new self();

        $attributes = $reflection->getAttributes($class);

        if (empty($attributes)) {
            return $instance;
        }

        $attribute = reset($attributes);
        $args = $attribute->getArguments();

        foreach ($args as $attribute => $value) {
            $instance->{$attribute} = $value;
        }

        return $instance;
    }
}
