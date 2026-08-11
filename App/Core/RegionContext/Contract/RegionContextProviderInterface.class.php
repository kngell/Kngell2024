<?php

declare(strict_types=1);

interface RegionContextProviderInterface
{
    public function getRegionCode(): string;

    public function getRegionEntity(): ?Region;

    public function getCurrencyCode(): string;

    public function getCurrencyEntity(): ?Currency;

    public function getLocale(): string;

    public function getLocaleData(): array;

    public function getTaxRate(): float;

    public function getNumberFormat(): array;

    public function getTimezone(): string;

    public function isRegionExplicit(): bool;

    public function clearCache(): void;

    public function getCacheStats(): array;

    public function warmUpCache(): void;
}