<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_PARAMETER)]
readonly class Qualifier
{
    public string $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
