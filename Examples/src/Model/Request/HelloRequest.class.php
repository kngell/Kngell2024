<?php

declare(strict_types=1);
readonly class HelloRequest
{
    public function __construct(
        private string $name
    ) {
    }

    /**
     * Get the value of name.
     */
    public function getName()
    {
        return $this->name;
    }
}
