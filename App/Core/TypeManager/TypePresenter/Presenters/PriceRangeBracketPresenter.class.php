<?php

declare(strict_types=1);

use Brick\Money\Money;

class PriceRangeBracketPresenter implements TypePresenterInterface
{
    public function __construct(
        private MoneyPresenter $moneyPresenter,
        private TranslatorServiceInterface $translator,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof PriceRangeBracket;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof PriceRangeBracket) {
            return (string) $value;
        }

        // Get display preferences from attributes
        $showProductCount = false;
        $format = 'range'; // 'range', 'min', 'max', 'label'
        $separator = ' - ';
        $showCurrency = true;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $formatAttr = $attributes[0]->newInstance();
                $showProductCount = $formatAttr->showProductCount ?? $showProductCount;
                $format = $formatAttr->style ?? $format;
                $separator = $formatAttr->separator ?? $separator;
                $showCurrency = $formatAttr->showCurrency ?? $showCurrency;
            }
        }

        $min = $value->getMin();
        $max = $value->getMax();
        $label = $value->getLabel();
        $productCount = $value->getProductCount();

        // Format based on requested format
        $formattedValue = match($format) {
            'min' => $this->formatPrice($min, $showCurrency, $regionContext),
            'max' => $this->formatPrice($max, $showCurrency, $regionContext),
            'label' => $label ?: $this->generateLabel($min, $max, $separator),
            'range' => $this->formatRange($min, $max, $label, $separator, $showCurrency, $regionContext),
            default => $this->formatRange($min, $max, $label, $separator, $showCurrency, $regionContext),
        };

        // Append product count if requested and available
        if ($showProductCount && $productCount !== null) {
            $formattedValue .= $this->formatProductCount($productCount);
        }

        return $formattedValue;
    }

    private function formatPrice(
        ?Money $price,
        bool $showCurrency,
        ?RegionContextInterface $regionContext,
    ): string {
        if ($price === null) {
            return $this->translator->translate('price_range.unlimited');
        }

        // Delegate to MoneyPresenter for proper formatting
        return $this->moneyPresenter->display(
            $price,
            null,
            $regionContext,
        );
    }

    private function formatRange(
        ?Money $min,
        ?Money $max,
        ?string $label,
        string $separator,
        bool $showCurrency,
        ?RegionContextInterface $regionContext,
    ): string {
        // Use label if provided
        if ($label !== null && $label !== '') {
            return $label;
        }

        // Generate range string
        $parts = [];

        if ($min !== null) {
            $parts[] = $this->formatPrice($min, $showCurrency, $regionContext);
        }

        if ($max !== null) {
            $parts[] = $this->formatPrice($max, $showCurrency, $regionContext);
        }

        if (empty($parts)) {
            return $this->translator->translate('price_range.empty');
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return implode($separator, $parts);
    }

    private function generateLabel(?Money $min, ?Money $max, string $separator): string
    {
        $parts = [];

        if ($min !== null) {
            $parts[] = (string) $min->getAmount();
        }

        if ($max !== null) {
            $parts[] = (string) $max->getAmount();
        }

        if (empty($parts)) {
            return $this->translator->translate('price_range.any');
        }

        if (count($parts) === 1) {
            $prefix = $min !== null ? '≥' : '≤';
            return $prefix . $parts[0];
        }

        return implode($separator, $parts);
    }

    private function formatProductCount(int $count): string
    {
        if ($count === 0) {
            return '';
        }

        return sprintf(
            ' (%s %s)',
            $this->translator->translate('product_count', ['%count%' => $count]),
            $this->translator->translate($count === 1 ? 'product_singular' : 'product_plural'),
        );
    }
}