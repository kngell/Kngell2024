<?php

declare(strict_types=1);

class WeightPresenter implements TypePresenterInterface
{
    public function __construct(
        private RegionContextInterface $regionContext,
        private TranslatorServiceInterface $translator,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof Weight;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof Weight) {
            return (string) $value;
        }

        $regionContext = $regionContext ?? $this->regionContext;

        // Get display preferences from property attributes
        $decimals = 2;
        $displayUnit = null; // null = auto-detect based on region
        $showUnit = true;
        $compact = false;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $format = $attributes[0]->newInstance();
                $decimals = $format->decimals ?? $decimals;
                $displayUnit = $format->unit ?? $displayUnit;
                $showUnit = $format->showUnit ?? $showUnit;
                $compact = $format->compact ?? $compact;
            }
        }

        // Auto-detect unit based on region if not specified
        if ($displayUnit === null) {
            $displayUnit = $this->getDefaultUnitForRegion($regionContext);
        }

        // Convert to target unit if needed
        $displayWeight = $value;
        if ($displayUnit instanceof WeightUnits && $value->getUnit() !== $displayUnit) {
            $displayWeight = $value->convertTo($displayUnit);
        }

        // Format the value
        $formattedValue = $regionContext->formatNumber($displayWeight->getValue(), $decimals);

        if (!$showUnit) {
            return $formattedValue;
        }

        if ($compact) {
            $unitSymbol = $displayWeight->getUnit()->getSymbol();
            return $formattedValue . $unitSymbol;
        }

        $unitSymbol = $displayWeight->getUnit()->getSymbol();
        $unitName = $this->getUnitName($displayWeight->getUnit());

        return $formattedValue . ' ' . $unitSymbol;
    }

    private function getDefaultUnitForRegion(RegionContextInterface $regionContext): WeightUnits
    {
        $locale = $regionContext->getLocale();

        // US, UK, and other imperial-using countries
        $imperialCountries = ['en_US', 'en_GB', 'en_CA'];

        if (in_array($locale, $imperialCountries)) {
            return WeightUnits::POUND;
        }

        // Metric by default
        return WeightUnits::KILOGRAM;
    }

    private function getUnitName(WeightUnits $unit): string
    {
        return match($unit) {
            WeightUnits::GRAM => $this->translator->translate('weight.grams'),
            WeightUnits::KILOGRAM => $this->translator->translate('weight.kilograms'),
            WeightUnits::POUND => $this->translator->translate('weight.pounds'),
            WeightUnits::OUNCE => $this->translator->translate('weight.ounces'),
            default => $unit->value,
        };
    }
}