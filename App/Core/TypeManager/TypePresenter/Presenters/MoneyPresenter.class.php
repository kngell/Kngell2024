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

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof Money) {
            return (string) $value;
        }

        $region = $regionContext ?? $this->regionContext;
        $style = $this->getStyle($property);
        $amount = $value->getAmount()->toFloat();
        $currencyCode = $value->getCurrency()->getCurrencyCode();

        return match($style) {
            'compact' => $region->formatCurrency($amount, $currencyCode),
            'symbol-only' => $this->currencyProvider->getCurrencySymbol($currencyCode) ?? $currencyCode,
            'code-only' => $currencyCode,
            'accounting' => $this->formatAccounting($amount, $currencyCode, $region),
            default => $region->formatCurrency($amount, $currencyCode),
        };
    }

    private function getStyle(?ReflectionProperty $property): string
    {
        if ($property === null) {
            return 'standard';
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            return $format->style ?? 'standard';
        }
        return 'standard';
    }

    private function formatAccounting(float $amount, string $currencyCode, RegionContextInterface $region): string
    {
        $formattedAmount = $region->formatNumber(abs($amount));
        if ($amount < 0) {
            return '(' . $currencyCode . ' ' . $formattedAmount . ')';
        }
        return $currencyCode . ' ' . $formattedAmount;
    }
}