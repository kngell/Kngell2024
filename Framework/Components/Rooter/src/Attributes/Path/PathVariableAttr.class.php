<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_PARAMETER)]
readonly class PathVariableAttr
{
    public function __construct(public string|null $name = null)
    {
    }
}