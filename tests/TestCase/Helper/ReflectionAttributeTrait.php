<?php

namespace SwaggerBake\Test\TestCase\Helper;

use ReflectionAttribute;
use ReflectionMethod;

trait ReflectionAttributeTrait
{
    private function mockReflectionMethod(string $class, array $arguments): ReflectionMethod
    {
        $mockReflectionMethod = $this->createPartialMock(ReflectionMethod::class, ['getAttributes']);
        $mockReflectionMethod
            ->expects($this->once())
            ->method('getAttributes')
            ->with($class)
            ->will(
                $this->returnValue([
                    $this->mockReflectionAttribute($class, $arguments)
                ])
            );

        return $mockReflectionMethod;
    }

    /**
     * Creates a mock instance of ReflectionAttribute and returns it in an array
     *
     * @param string $class
     * @param array $arguments
     * @return ReflectionAttribute
     */
    private function mockReflectionAttribute(string $class, array $arguments): ReflectionAttribute
    {
        $mock = $this->createPartialMock(ReflectionAttribute::class, ['newInstance', ]);
        $mock->expects($this->once())
            ->method('newInstance')
            ->willReturn(new $class(...$arguments));

        return $mock;
    }

    /**
     * Is the array an associative array?
     *
     * @param array $arr
     * @return bool
     */
    private function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}