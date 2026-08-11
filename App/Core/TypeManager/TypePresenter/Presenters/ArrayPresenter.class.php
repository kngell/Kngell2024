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
        // If it isn't an array at all, it shouldn't be handled by this presenter
        if (!is_array($value)) {
            return false;
        }

        if (ArrayUtils::isObjectList($value)) {
            return false;
        }

        if (!empty($value) && is_object(reset($value))) {
            return false;
        }

        return true;
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        if (empty($value)) {
            return $this->emptyText;
        }

        // Defensive Guard: If a recursive call accidentally passes a scalar string, return it as-is
        if (!is_array($value)) {
            return (string) $value;
        }

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

        // Handle associative metadata layouts cleanly
        if (ArrayUtils::isAssoc($value)) {
            return $this->formatAssociativeArray($value, $separator);
        }

        // Limit items if specified (For flat lists)
        if ($maxItems !== null && count($value) > $maxItems) {
            $visible = array_slice($value, 0, $maxItems);
            $remaining = count($value) - $maxItems;

            $result = implode($separator, array_map([$this, 'stringifyElement'], $visible));
            $result .= $this->translator->translate('common.and_n_more', ['count' => $remaining]);

            if ($showCount) {
                $result = '(' . count($value) . ') ' . $result;
            }

            return $result;
        }

        // CRITICAL FIX: Use a safe mapping callback that handles nested arrays/objects gracefully
        $result = implode($separator, array_map([$this, 'stringifyElement'], $value));

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
                // Safe recursion
                $value = $this->display($value);
            }
            $pairs[] = $key . ': ' . $value;
        }
        return implode($separator, $pairs);
    }

    /**
     * Safe fallback stringifier to guarantee array_map never receives invalid types.
     */
    private function stringifyElement(mixed $element): string
    {
        if (is_array($element)) {
            return $this->formatAssociativeArray($element, $this->defaultSeparator);
        }
        if (is_object($element)) {
            return method_exists($element, '__toString') ? (string) $element : '[Object]';
        }
        return (string) $element;
    }
}