<?php

declare(strict_types=1);

interface RegularPageConfigInterface
{
    public function getEnumClass(): string;

    public function getAssets(): array;

    public function getExpectedControllerClass(): ?string;
}