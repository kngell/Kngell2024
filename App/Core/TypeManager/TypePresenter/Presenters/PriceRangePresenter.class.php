<?php

declare(strict_types=1);

use Brick\Money\Money;

class PriceRangePresenter implements TypePresenterInterface
{
    public function __construct(
        private PriceRangeBracketPresenter $bracketPresenter,
        private TranslatorServiceInterface $translator,
        private MoneyPresenter $moneyPresenter,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof PriceRange;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof PriceRange) {
            return (string) $value;
        }

        // Get display preferences
        $showAllBrackets = false;
        $bracketSeparator = ' | ';
        $showMinMax = true;
        $format = 'overview'; // 'overview', 'brackets', 'detailed'

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $formatAttr = $attributes[0]->newInstance();
                $showAllBrackets = $formatAttr->showAllBrackets ?? $showAllBrackets;
                $bracketSeparator = $formatAttr->bracketSeparator ?? $bracketSeparator;
                $showMinMax = $formatAttr->showMinMax ?? $showMinMax;
                $format = $formatAttr->style ?? $format;
            }
        }

        $brackets = $value->getBrackets();
        $minPrice = $value->getMinPrice();
        $maxPrice = $value->getMaxPrice();

        return match($format) {
            'overview' => $this->formatOverview($brackets, $minPrice, $maxPrice, $showMinMax, $regionContext),
            'brackets' => $this->formatBrackets($brackets, $bracketSeparator, $regionContext),
            'detailed' => $this->formatDetailed($brackets, $minPrice, $maxPrice, $regionContext),
            default => $this->formatOverview($brackets, $minPrice, $maxPrice, $showMinMax, $regionContext),
        };
    }

    private function formatOverview(
        array $brackets,
        ?Money $minPrice,
        ?Money $maxPrice,
        bool $showMinMax,
        ?RegionContextInterface $regionContext,
    ): string {
        if (empty($brackets)) {
            return $this->translator->translate('price_range.no_brackets');
        }

        // Show min-max range by default
        if ($showMinMax && $minPrice !== null && $maxPrice !== null) {
            $min = $this->moneyPresenter->display($minPrice, null, $regionContext);
            $max = $this->moneyPresenter->display($maxPrice, null, $regionContext);
            return sprintf(
                '%s - %s (%d %s)',
                $min,
                $max,
                count($brackets),
                $this->translator->translate('price_range.brackets'),
            );
        }

        // Show only first bracket as summary
        $firstBracket = $brackets[0] ?? null;
        if ($firstBracket !== null) {
            return $this->bracketPresenter->display($firstBracket, null, $regionContext);
        }

        return $this->translator->translate('price_range.empty');
    }

    private function formatBrackets(
        array $brackets,
        string $separator,
        ?RegionContextInterface $regionContext,
    ): string {
        if (empty($brackets)) {
            return $this->translator->translate('price_range.no_brackets');
        }

        $formattedBrackets = array_map(
            fn ($bracket) => $this->bracketPresenter->display($bracket, null, $regionContext),
            $brackets,
        );

        return implode($separator, $formattedBrackets);
    }

    private function formatDetailed(
        array $brackets,
        ?Money $minPrice,
        ?Money $maxPrice,
        ?RegionContextInterface $regionContext,
    ): string {
        if (empty($brackets)) {
            return $this->translator->translate('price_range.no_brackets');
        }

        $parts = [];

        // Add range summary
        if ($minPrice !== null && $maxPrice !== null) {
            $parts[] = sprintf(
                '%s: %s - %s',
                $this->translator->translate('price_range.range'),
                $this->moneyPresenter->display($minPrice, null, $regionContext),
                $this->moneyPresenter->display($maxPrice, null, $regionContext),
            );
        }

        // Add each bracket
        foreach ($brackets as $index => $bracket) {
            $bracketLabel = $bracket->getLabel() ?: $this->translator->translate('price_range.bracket') . ' ' . ($index + 1);
            $bracketDisplay = $this->bracketPresenter->display($bracket, null, $regionContext);
            $parts[] = $bracketLabel . ': ' . $bracketDisplay;
        }

        return implode("\n", $parts);
    }
}