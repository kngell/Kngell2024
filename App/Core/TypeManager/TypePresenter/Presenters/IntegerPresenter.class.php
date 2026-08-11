<?php

declare(strict_types=1);

final class IntegerPresenter implements TypePresenterInterface
{
    public function __construct(
        private RegionContextInterface $regionContext,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_int($value);
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        $region = $regionContext ?? $this->regionContext;
        $number = (int) $value;

        $attributes = $property?->getAttributes(DisplayFormat::class);
        $style = 'standard';

        if (!empty($attributes)) {
            $format = $attributes[0]->newInstance();
            $style = $format->style ?? 'standard';
        }

        if ($style === 'compact') {
            return $this->formatCompact($number);
        }

        return $region->formatNumber($number, 0);
    }

    private function formatCompact(int $number): string
    {
        if ($number === 0) {
            return '0';
        }

        $abs = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($abs >= 1000000) {
            return $sign . round($abs / 1000000, 1) . 'M';
        }
        if ($abs >= 1000) {
            return $sign . round($abs / 1000, 1) . 'K';
        }
        return $sign . (string) $abs;
    }
}