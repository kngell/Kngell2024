<?php

declare(strict_types=1);

final class Formatter implements FormatterInterface
{
    public function __construct(
        private CurrencyResolverInterface $currencyResolver,
        private FallbackSymbolProviderInterface $fallbackSymbolProvider,
    ) {
    }

    public function formatNumber(float $number, array $format, ?int $decimals = null): string
    {
        $decimals = $decimals ?? $format['fraction_digits'] ?? 2;

        return number_format(
            $number,
            $decimals,
            $format['decimal_separator'] ?? '.',
            $format['thousands_separator'] ?? ',',
        );
    }

    public function formatCurrency(float $amount, string $currencyCode, array $numberFormat): string
    {
        $symbol = $this->getCurrencySymbol($currencyCode);
        $formattedAmount = $this->formatNumber($amount, $numberFormat);

        if ($numberFormat['currency_position'] === 'after') {
            return $formattedAmount . ' ' . $symbol;
        } else {
            return $symbol . $formattedAmount;
        }
    }

    public function formatDate(DateTimeInterface $date, string $format): string
    {
        return $date->format($format);
    }

    public function formatDateTime(DateTimeInterface $dateTime, string $format): string
    {
        return $dateTime->format($format);
    }

    public function getCurrencySymbol(string $currencyCode): string
    {
        $currency = $this->currencyResolver->getCurrencyByCode(strtoupper($currencyCode));

        if ($currency) {
            return $currency->getCurrencySymbol()
                ?? $currency->getSymbol()
                ?? $this->fallbackSymbolProvider->getFallbackSymbol($currencyCode);
        }

        return $this->fallbackSymbolProvider->getFallbackSymbol($currencyCode);
    }
}