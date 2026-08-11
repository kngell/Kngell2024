<?php

declare(strict_types=1);

final class CustomReflection
{
    /** @var array<class-string, self> */
    private static array $instances = [];

    /** @var array<class-string, array<string, ReflectionProperty>> */
    private static array $propertyCache = [];

    /** @var array<class-string, array<string, ReflectionMethod>> */
    private static array $methodCache = [];

    private function __construct(
        private readonly ReflectionClass $classReflection,
    ) {
    }

    public function getClass(): ReflectionClass
    {
        return $this->classReflection;
    }

    public function getProperty(string $propertyName): ?ReflectionProperty
    {
        $className = $this->classReflection->getName();

        if (!isset(self::$propertyCache[$className][$propertyName])) {
            try {
                $property = $this->classReflection->getProperty($propertyName);
                $property->setAccessible(true);
                self::$propertyCache[$className][$propertyName] = $property;
            } catch (ReflectionException) {
                return null;
            }
        }

        return self::$propertyCache[$className][$propertyName];
    }

    public function getMethod(string $methodName): ?ReflectionMethod
    {
        $className = $this->classReflection->getName();

        if (!isset(self::$methodCache[$className][$methodName])) {
            try {
                $method = $this->classReflection->getMethod($methodName);
                $method->setAccessible(true);
                self::$methodCache[$className][$methodName] = $method;
            } catch (ReflectionException) {
                return null;
            }
        }

        return self::$methodCache[$className][$methodName];
    }

    public function getPropertyValue(object $object, string $propertyName): mixed
    {
        $property = $this->getProperty($propertyName);

        if ($property === null) {
            throw new RuntimeException("Property {$propertyName} does not exist");
        }

        return $property->getValue($object);
    }

    public function setPropertyValue(object $object, string $propertyName, mixed $value): void
    {
        $property = $this->getProperty($propertyName);

        if ($property === null) {
            throw new RuntimeException("Property {$propertyName} does not exist");
        }

        $property->setValue($object, $value);
    }

    public function callMethod(object $object, string $methodName, mixed ...$args): mixed
    {
        $method = $this->getMethod($methodName);

        if ($method === null) {
            throw new RuntimeException("Method {$methodName} does not exist");
        }

        return $method->invoke($object, ...$args);
    }

    /**
     * LEGACY API — do not remove yet.
     *
     * @deprecated Use getClass() instead
     */
    public function getObject(): ReflectionObject
    {
        return new ReflectionObject(
            $this->classReflection->newInstanceWithoutConstructor(),
        );
    }

    public static function getInstance(object|string $class): self
    {
        $className = is_object($class) ? $class::class : $class;

        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new self(new ReflectionClass($className));
        }

        return self::$instances[$className];
    }

    public static function clearCache(): void
    {
        self::$instances = [];
        self::$propertyCache = [];
        self::$methodCache = [];
    }
}