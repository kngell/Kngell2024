<?php

declare(strict_types=1);

class Pair
{
    public function __construct(private readonly mixed $key, private readonly mixed $value)
    {
    }

    /**
     * Get the value of key.
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * Get the value of value.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
