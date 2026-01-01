<?php

declare(strict_types=1);

interface CurrencyResolverInterface
{
    public function resolveCurrencyForRegion(string $regionCode): ?Currency;

    public function getDefaultCurrency(): ?Currency;

    public function getCurrencyById(int $currencyId): ?Currency;

    public function getCurrencyByCode(string $currencyCode): ?Currency;

    public function getAllActiveCurrencies(): array;
}