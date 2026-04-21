<?php

declare(strict_types=1);

abstract class AbstractDTOBaseResponse implements DTOResponseInterface
{
    public function __construct(
        protected readonly array $image,
        protected readonly bool $isDefault = false,
    ) {
    }

    public function getImage(): array
    {
        return $this->image;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getImageAlt(): ?string
    {
        return $this->image['fallback']['alt'] ?? null;
    }
}