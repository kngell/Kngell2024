<?php

declare(strict_types=1);

class NullObject implements NullObjectInterface
{
    public function __construct(private null|Object $object = null)
    {
    }

    public function __call($name, $arguments)
    {
        // Do nothing or return self for chaining
        return $this;
    }

    public function __get($name)
    {
        // Return null for any property access
        return null;
    }

    public function __set($name, $value)
    {
        // Do nothing
    }

    public function __toString()
    {
        return '';
    }

    public function isNull(): bool
    {
        return true;
    }

    public function isSuccess(): bool
    {
        return false;
    }
}