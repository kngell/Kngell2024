<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_PARAMETER)]
readonly class RequestBody
{
    public function __construct(public bool $required = false)
    {
    }
}