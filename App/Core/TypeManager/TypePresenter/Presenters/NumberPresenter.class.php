<?php

declare(strict_types=1);

final class NumberPresenter implements TypePresenterInterface
{
    public function __construct(
        private RegionContextInterface $regionContext,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_numeric($value) && !is_int($value) && !$this->isIdField($property);
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        $region = $regionContext ?? $this->regionContext;
        $number = (float) $value;

        $options = $this->getFormatOptions($property);

        $formatted = match($options['style']) {
            'percent' => $region->formatNumber($number * 100, $options['decimals']) . '%',
            'scientific' => sprintf("%.{$options['decimals']}e", $number),
            'compact' => $this->formatCompact($number, $options['decimals']),
            default => $region->formatNumber($number, $options['decimals']),
        };

        return $options['prefix'] . $formatted . $options['suffix'];
    }

    private function getFormatOptions(?ReflectionProperty $property): array
    {
        $defaults = [
            'decimals' => 2,
            'style' => 'standard',
            'prefix' => '',
            'suffix' => '',
        ];

        if ($property === null) {
            return $defaults;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            return [
                'decimals' => $format->decimals ?? $defaults['decimals'],
                'style' => $format->style ?? $defaults['style'],
                'prefix' => $format->prefix ?? $defaults['prefix'],
                'suffix' => $format->suffix ?? $defaults['suffix'],
            ];
        }

        return $defaults;
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

    private function isIdField(?ReflectionProperty $property): bool
    {
        if ($property === null) {
            return false;
        }
        $name = strtolower($property->getName());
        return str_ends_with($name, 'id') || !empty($property->getAttributes(EntityFieldId::class));
    }
}