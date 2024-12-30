<?php

declare(strict_types=1);
final class CustomReflection
{
    /** @var CustomReflection */
    private static $instance;

    private static $object;

    private function __construct(private ReflectionObject $reflection)
    {
    }

    public static function getInstance(object $object) : self
    {
        if (! isset(static::$instance) || static::$object !== $object) {
            static::$object = $object;
            static::$instance = new static(new ReflectionObject($object));
        }
        return static::$instance;
    }

    public function getObject() : ReflectionObject
    {
        return $this->reflection;
    }
}