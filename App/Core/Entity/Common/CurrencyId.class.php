<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_PROPERTY)]
class CurrencyId
{
    private string|null $name;

    public function __construct(string|null $name)
    {
        $this->name = $name;
    }
}