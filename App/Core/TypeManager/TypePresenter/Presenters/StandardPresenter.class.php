<?php

declare(strict_types=1);

class StandardPresenter implements TypePresenterInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_scalar($value);
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_numeric($value)) {
            if ($property !== null) {
                $name = $property->getName();

                if (str_ends_with(strtolower($name), 'id')) {
                    return (string) $value;
                }
                if (!empty($property->getAttributes(EntityFieldId::class))) {
                    return (string) $value;
                }
            }

            return $this->formatNumber($value, $property);
        }

        return $value;
    }

    private function formatNumber(mixed $value, ?ReflectionProperty $property): string
    {
        $isIntegerType = false;
        if ($property !== null && $property->hasType()) {
            $type = $property->getType();

            if ($type instanceof ReflectionNamedType && $type->getName() === 'int') {
                $isIntegerType = true;
            }
        }

        $numericValue = $isIntegerType ? (int) $value : (float) $value;

        // 3. Resolve formatting rules
        $decimals = $isIntegerType ? 0 : 2;
        $decimalSeparator = '.';
        $thousandsSeparator = ',';

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $format = $attributes[0]->newInstance();
                $decimals = $format->decimals ?? $decimals;
            }
        }

        return number_format((float) $numericValue, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}