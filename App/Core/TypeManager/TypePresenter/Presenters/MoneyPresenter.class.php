<?php

declare(strict_types=1);

use Brick\Money\Money;

final class MoneyPresenter implements TypePresenterInterface
{
    public function __construct(
        private RegionContextInterface $regionContext,
        private CurrencyCodeProviderInterface $currencyProvider,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof Money;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        if (!$value instanceof Money) {
            return $value;
        }

        $regionContext = $regionContext ?? $this->regionContext;
        $locale = $regionContext->getLocale();

        // Check for display format attributes
        $formatStyle = $this->getFormatStyle($property);

        return $this->formatMoney($value, $locale, $formatStyle, $regionContext);
    }

    private function getFormatStyle(?ReflectionProperty $property): string
    {
        if ($property === null) {
            return 'standard';
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        if (!empty($attributes)) {
            $format = $attributes[0]->newInstance();
            return $format->style ?? 'standard';
        }

        return 'standard';
    }

    private function formatMoney(Money $money, string $locale, string $style, RegionContextInterface $regionContext): string
    {
        $currencyCode = $money->getCurrency()->getCurrencyCode();
        $amount = $money->getAmount()->toFloat();

        // Use currency provider for symbol lookup
        $symbol = $this->currencyProvider->getCurrencySymbol($currencyCode) ?? $currencyCode;

        switch ($style) {
            case 'compact':
                return $regionContext->formatCurrency($amount, $currencyCode);
            case 'symbol-only':
                return $symbol;
            case 'code-only':
                return $currencyCode;
            case 'accounting':
                // Accounting format: (USD 100.00) for negative
                $formattedAmount = $regionContext->formatNumber(abs($amount));
                if ($amount < 0) {
                    return '(' . $currencyCode . ' ' . $formattedAmount . ')';
                }
                return $currencyCode . ' ' . $formattedAmount;
            case 'standard':
            default:
                return $regionContext->formatCurrency($amount, $currencyCode);
        }
    }
}