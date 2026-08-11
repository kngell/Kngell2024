<?php

declare(strict_types=1);

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class Inject
{
    public function __construct(
        private ?string $id = null,
        private bool $lazy = true,
        private ?string $method = null,
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isLazy(): bool
    {
        return $this->lazy;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }
}