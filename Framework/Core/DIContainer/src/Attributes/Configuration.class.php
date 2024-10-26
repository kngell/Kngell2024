<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Configuration extends Service
{
    public function __construct(?string $name)
    {
        parent::__construct($name);
    }
}
