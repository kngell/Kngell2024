<?php

declare(strict_types=1);

class DimensionsPresenter implements TypePresenterInterface
{
    public function __construct(
        private RegionContextInterface $regionContext,
        private TranslatorServiceInterface $translator,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof Dimensions;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof Dimensions) {
            return (string) $value;
        }

        $regionContext = $regionContext ?? $this->regionContext;

        // Get display preferences
        $decimals = 1;
        $displayUnit = null; // null = auto-detect
        $format = 'dimensions'; // 'dimensions', 'volume', 'both'
        $separator = ' × ';
        $showUnit = true;
        $compact = false;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $formatAttr = $attributes[0]->newInstance();
                $decimals = $formatAttr->decimals ?? $decimals;
                $displayUnit = $formatAttr->unit ?? $displayUnit;
                $format = $formatAttr->style ?? $format;
                $separator = $formatAttr->separator ?? $separator;
                $showUnit = $formatAttr->showUnit ?? $showUnit;
                $compact = $formatAttr->compact ?? $compact;
            }
        }

        // Auto-detect unit
        if ($displayUnit === null) {
            $displayUnit = $this->getDefaultUnitForRegion($regionContext);
        }

        // Convert dimensions if needed
        $displayDimensions = $value;
        if ($displayUnit && $value->getUnit() !== $displayUnit) {
            $displayDimensions = $value->convertTo($displayUnit);
        }

        // Format based on requested format
        switch ($format) {
            case 'volume':
                return $this->formatVolume($displayDimensions, $regionContext, $decimals, $showUnit);
            case 'both':
                $dimensions = $this->formatDimensions($displayDimensions, $regionContext, $decimals, $separator, $showUnit, $compact);
                $volume = $this->formatVolume($displayDimensions, $regionContext, $decimals, $showUnit);
                return $dimensions . ' (' . $volume . ')';
            case 'dimensions':
            default:
                return $this->formatDimensions($displayDimensions, $regionContext, $decimals, $separator, $showUnit, $compact);
        }
    }

    private function formatDimensions(
        Dimensions $dimensions,
        RegionContextInterface $regionContext,
        int $decimals,
        string $separator,
        bool $showUnit,
        bool $compact,
    ): string {
        $parts = [];

        if ($dimensions->getLength() !== null) {
            $parts[] = $regionContext->formatNumber($dimensions->getLength(), $decimals);
        }

        if ($dimensions->getWidth() !== null) {
            $parts[] = $regionContext->formatNumber($dimensions->getWidth(), $decimals);
        }

        if ($dimensions->getHeight() !== null) {
            $parts[] = $regionContext->formatNumber($dimensions->getHeight(), $decimals);
        }

        if (empty($parts)) {
            return $this->translator->translate('dimensions.not_specified');
        }

        $formatted = implode($separator, $parts);

        if ($showUnit && !$compact) {
            $formatted .= ' ' . $dimensions->getUnit()->value;
        } elseif ($showUnit && $compact) {
            $formatted .= $dimensions->getUnit()->value;
        }

        return $formatted;
    }

    private function formatVolume(
        Dimensions $dimensions,
        RegionContextInterface $regionContext,
        int $decimals,
        bool $showUnit,
    ): string {
        $volume = $dimensions->getVolume();

        if ($volume === null) {
            return $this->translator->translate('volume.not_calculable');
        }

        $formattedVolume = $regionContext->formatNumber($volume, $decimals);

        if ($showUnit) {
            $volumeUnit = $this->getVolumeUnit($dimensions->getUnit());
            return $formattedVolume . ' ' . $volumeUnit;
        }

        return $formattedVolume;
    }

    private function getDefaultUnitForRegion(RegionContextInterface $regionContext): Units
    {
        $locale = $regionContext->getLocale();

        // US and other imperial-using countries
        $imperialCountries = ['en_US', 'en_GB'];

        if (in_array($locale, $imperialCountries)) {
            return Units::INCH;
        }

        // Metric by default
        return Units::CM;
    }

    private function getVolumeUnit(Units $unit): string
    {
        return match($unit) {
            Units::MM => 'mm³',
            Units::CM => 'cm³',
            Units::M => 'm³',
            Units::INCH => 'in³',
            Units::FT => 'ft³',
            default => $unit->value . '³',
        };
    }
}