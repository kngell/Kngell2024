<?php

declare(strict_types=1);
final class CustomReflection
{
    /** @var CustomReflection */
    private static $instance;

    private function __construct(private ReflectionObject $reflection)
    {
    }

    public static function getInstance(object $object) : self
    {
        if (! isset(self::$instance)) {
            self::$instance = new static(new ReflectionObject($object));
        }
        return self::$instance;
    }

    public function getObject() : ReflectionObject
    {
        return $this->reflection;
    }
}