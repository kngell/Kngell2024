<?php

declare(strict_types=1);

class ArrayPresenter implements TypePresenterInterface
{
    public function __construct(
        private TranslatorServiceInterface $translator,
        private string $defaultSeparator = ', ',
        private string $emptyText = '',
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if (!is_array($value) || (is_array($value) && ArrayUtils::isObjectList($value))) {
            return false;
        }

        if (!empty($value) && is_object(reset($value))) {
            return false;
        }
        if (is_object($value) || $value instanceof Entity) {
            return false;
        }

        return true;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): string
    {
        if (empty($value)) {
            return $this->emptyText;
        }

        // Get formatting options from property attributes
        $separator = $this->defaultSeparator;
        $maxItems = null;
        $showCount = false;

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $format = $attributes[0]->newInstance();
                $separator = $format->separator ?? $separator;
                $maxItems = $format->maxItems ?? $maxItems;
                $showCount = $format->showCount ?? $showCount;
            }
        }

        // Handle associative arrays differently
        if (is_array($value) && ArrayUtils::isAssoc($value)) {
            return $this->formatAssociativeArray($value, $separator);
        }

        // Limit items if specified
        if ($maxItems !== null && count($value) > $maxItems) {
            $visible = array_slice($value, 0, $maxItems);
            $remaining = count($value) - $maxItems;

            $result = implode($separator, array_map('strval', $visible));
            $result .= $this->translator->translate('common.and_n_more', ['count' => $remaining]);

            if ($showCount) {
                $result = '(' . count($value) . ') ' . $result;
            }

            return $result;
        }

        $result = implode($separator, array_map('strval', $value));

        if ($showCount) {
            $result = '(' . count($value) . ') ' . $result;
        }

        return $result;
    }

    private function formatAssociativeArray(array $array, string $separator): string
    {
        $pairs = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->display($value);
            }
            $pairs[] = $key . ': ' . $value;
        }
        return implode($separator, $pairs);
    }
}