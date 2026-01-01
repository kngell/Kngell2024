<?php

declare(strict_types=1);

class FloatPresenter implements TypePresenterInterface
{
    public function __construct(
        private RegionContextInterface $regionContext,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_float($value) || (is_numeric($value) && !is_int($value));
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        $regionContext = $regionContext ?? $this->regionContext;

        // Default formatting
        $decimals = 2;
        $style = 'standard';
        $suffix = '';
        $prefix = '';

        // Check for display format attributes
        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $format = $attributes[0]->newInstance();
                $decimals = $format->decimals ?? $decimals;
                $style = $format->style ?? $style;
                $suffix = $format->suffix ?? $suffix;
                $prefix = $format->prefix ?? $prefix;
            }
        }

        $number = (float) $value;

        switch ($style) {
            case 'percent':
                $formatted = $regionContext->formatNumber($number * 100, $decimals) . '%';
                break;
            case 'currency':
                // For currency, use MoneyPresenter instead
                $formatted = $regionContext->formatCurrency($number, 'USD', $decimals);
                break;
            case 'scientific':
                $formatted = sprintf("%.{$decimals}e", $number);
                break;
            case 'compact':
                $formatted = $this->formatCompact($number, $decimals);
                break;
            case 'standard':
            default:
                $formatted = $regionContext->formatNumber($number, $decimals);
                break;
        }

        // Add prefix and suffix
        if ($prefix) {
            $formatted = $prefix . $formatted;
        }
        if ($suffix) {
            $formatted .= $suffix;
        }

        return $formatted;
    }

    private function formatCompact(float $number, int $decimals): string
    {
        if ($number === 0.0) {
            return '0';
        }

        $abs = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($abs >= 1000000) {
            return $sign . round($abs / 1000000, $decimals) . 'M';
        }

        if ($abs >= 1000) {
            return $sign . round($abs / 1000, $decimals) . 'K';
        }

        return $sign . round($abs, $decimals);
    }
}