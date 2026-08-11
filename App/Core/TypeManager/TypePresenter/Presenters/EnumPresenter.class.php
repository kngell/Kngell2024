<?php

declare(strict_types=1);

final class EnumPresenter implements TypePresenterInterface
{
    public function __construct(
        private TranslatorServiceInterface $translator,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof UnitEnum || $value instanceof BackedEnum;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (!$value instanceof UnitEnum && !$value instanceof BackedEnum) {
            return (string) $value;
        }

        // Try translation first
        $enumClass = get_class($value);
        $enumName = $value->name;
        $translationKey = 'enum.' . strtolower($enumClass) . '.' . strtolower($enumName);

        if ($this->translator->has($translationKey)) {
            return $this->translator->translate($translationKey);
        }

        // Fallback to value or humanized name
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return $this->humanize($enumName);
    }

    private function humanize(string $text): string
    {
        $text = str_replace('_', ' ', $text);
        $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);
        return ucwords(strtolower($text));
    }
}