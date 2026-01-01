<?php

declare(strict_types=1);

interface RegionContextInterface
{
    public function getRegionCode(): string;

    public function getLocale(): string;

    public function getLocaleData(): array;

    public function getRegionEntity(): ?Region;

    public function getCurrencyCode(): string;

    public function getNumberFormat(): array;

    public function getDateFormat(): string;

    public function getDateTimeFormat(): string;

    public function getTimeFormat(): string;

    public function getFirstDayOfWeek(): int;

    public function getTimezone(): string;

    public function isRegionExplicit(): bool;

    public function formatNumber(float $number, ?int $decimals = null): string;

    public function formatCurrency(float $amount, ?string $currencyCode = null): string;

    public function formatDate(DateTimeInterface $date, ?string $format = null): string;

    public function formatDateTime(DateTimeInterface $dateTime, ?string $format = null): string;

    public function clearCache(): void;
}