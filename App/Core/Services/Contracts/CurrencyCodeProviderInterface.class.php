<?php

declare(strict_types=1);
interface CurrencyCodeProviderInterface
{
    public function getCurrencyCode(int $currencyId): string;

    public function getSystemDefaultCurrencyCode(): string;
}