<?php

declare(strict_types=1);
interface CurrencyCodeProviderInterface
{
    public function getCurrencyCode(int $currencyId): string;

    public function getSystemDefaultCurrencyCode(): string;

    public function isValidCurrency(string $currencyCode): bool;

    // NEW METHODS for region context
    public function getCurrencySymbol(string $currencyCode): ?string;

    public function getCurrencyById(int $currencyId): ?Currency;

    public function getCurrencyByCode(string $currencyCode): ?Currency;
}