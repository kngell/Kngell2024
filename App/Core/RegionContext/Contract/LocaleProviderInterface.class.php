<?php

declare(strict_types=1);

interface LocaleProviderInterface
{
    public function isValidLocale(string $locale): bool;

    public function getLocaleForRegion(string $regionCode): ?string;

    public function getLocaleData(string $locale): array;

    public function getSupportedLocales(): array;

    public function getDefaultLocale(): string;

    // Optional enhanced methods
    public function getFormattingForRegion(string $regionCode): ?array;

    public function getLocaleDataWithRegionFormatting(string $locale, string $regionCode): array;

    public function clearCache(): void;

    public function clearLocaleCache(string $locale): void;

    public function clearRegionCache(string $regionCode): void;
}