<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_PARAMETER)]
readonly class Alias
{
    /**
     * @param string[] $names Alternative parameter names to look for
     */
    public function __construct(public array $names = [])
    {
    }
}