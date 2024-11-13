<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_PARAMETER)]
readonly class HeaderParam
{
    public function __construct(
        public string|null $name = null,
        public string|int|float|bool|null $defaultValue = null,
        public bool $required = false
    ) {
    }
}