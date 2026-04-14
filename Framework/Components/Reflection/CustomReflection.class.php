<?php

declare(strict_types=1);
final class CustomReflection
{
    /** @var array<class-string, self> */
    private static array $instances = [];

    private function __construct(
        private readonly ReflectionClass $classReflection,
    ) {
    }

    /**
     * LEGACY API — do not remove yet.
     */
    public function getObject(): ReflectionObject
    {
        // Create a ReflectionObject on demand (cheap)
        return new ReflectionObject(
            $this->classReflection->newInstanceWithoutConstructor(),
        );
    }

    /**
     * NEW, CORRECT API.
     */
    public function getClass(): ReflectionClass
    {
        return $this->classReflection;
    }

    public static function getInstance(object $object): self
    {
        $class = $object::class;

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new self(
                new ReflectionClass($class),
            );
        }

        return self::$instances[$class];
    }
}