<?php

declare(strict_types=1);

interface DTOResponseInterface
{
    public function getImage(): array;

    public function isDefault(): bool;

    public function getImageAlt(): ?string;
}