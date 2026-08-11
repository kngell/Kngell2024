<?php

declare(strict_types=1);

final class BooleanPresenter implements TypePresenterInterface
{
    public function __construct(
        private TranslatorServiceInterface $translator,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_bool($value);
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        // Handle null values - return empty string or null placeholder
        if ($value === null) {
            return $this->getNullPlaceholder($property) ?? '';
        }

        // If raw is explicitly requested, return the actual boolean value
        if ($this->shouldReturnRaw($property)) {
            return $value;
        }

        // If there's a DisplayFormat attribute with a specific style, use it
        $style = $this->getStyle($property);

        // Only apply special formatting if a style is explicitly set
        if ($style !== null && $style !== 'default') {
            return match($style) {
                'yesno' => $value ? $this->translator->translate('common.yes') : $this->translator->translate('common.no'),
                'truefalse' => $value ? $this->translator->translate('common.true') : $this->translator->translate('common.false'),
                'activeinactive' => $value ? $this->translator->translate('common.active') : $this->translator->translate('common.inactive'),
                'onoff' => $value ? $this->translator->translate('common.on') : $this->translator->translate('common.off'),
                default => $value ? $this->translator->translate('common.yes') : $this->translator->translate('common.no'),
            };
        }

        // Default: return the raw boolean value as string
        return $value ? 'true' : 'false';
    }

    private function getStyle(?ReflectionProperty $property): ?string
    {
        if ($property === null) {
            return null;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            if ($format->style !== null) {
                return $format->style;
            }
        }
        return null;
    }

    private function shouldReturnRaw(?ReflectionProperty $property): bool
    {
        if ($property === null) {
            return false;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            if ($format->raw === true) {
                return true;
            }
        }
        return false;
    }

    private function getNullPlaceholder(?ReflectionProperty $property): ?string
    {
        if ($property === null) {
            return null;
        }

        $attributes = $property->getAttributes(DisplayFormat::class);
        foreach ($attributes as $attribute) {
            $format = $attribute->newInstance();
            if ($format->nullPlaceholder !== null) {
                return $format->nullPlaceholder;
            }
        }
        return null;
    }
}