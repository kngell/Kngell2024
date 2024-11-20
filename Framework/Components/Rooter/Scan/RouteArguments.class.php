<?php

declare(strict_types=1);
class RouteArguments
{
    private string $name;
    private mixed $value;

    public function __construct(string $name, mixed $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get the value of name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value of value.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}