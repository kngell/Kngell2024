<?php

declare(strict_types=1);

class StandardPresenter implements TypePresenterInterface
{
    public function __construct(
        private ?TranslatorServiceInterface $translator = null,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_scalar($value) || $value === null;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $this->translator?->translate($value ? 'common.yes' : 'common.no') ?? ($value ? 'Yes' : 'No');
        }

        // Pass through strings, ints, floats - let specific presenters handle formatting
        return $value;
    }

    private function prepareBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'y', 'on', 'active']);
        }

        return (bool) $value;
    }
}