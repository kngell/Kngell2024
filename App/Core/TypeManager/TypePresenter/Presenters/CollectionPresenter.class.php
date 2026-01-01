<?php

declare(strict_types=1);

class CollectionPresenter implements TypePresenterInterface
{
    public function __construct(
        private TypePresenterFactoryInterface $presenterFactory,
        private TranslatorServiceInterface $translator,
        private string $defaultSeparator = ', ',
        private string $emptyText = '',
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof CollectionInterface ||
               (is_array($value) && ArrayUtils::isObjectList($value));
    }

    public function display(mixed $value, ?ReflectionProperty $property = null, ?RegionContextInterface $regionContext = null): mixed
    {
        if ($this->isEmpty($value)) {
            return $this->emptyText;
        }

        $items = $value instanceof CollectionInterface ? $value->all() : $value;

        $separator = $this->defaultSeparator;
        $maxItems = null;
        $showCount = false;
        $formatStyle = 'list'; // 'list', 'summary', 'compact', 'detailed'

        if ($property !== null) {
            $attributes = $property->getAttributes(DisplayFormat::class);
            if (!empty($attributes)) {
                $format = $attributes[0]->newInstance();
                $separator = $format->separator ?? $separator;
                $maxItems = $format->maxItems ?? $maxItems;
                $showCount = $format->showCount ?? $showCount;
                $formatStyle = $format->style ?? $formatStyle;
            }
        }

        // Format based on style
        return match($formatStyle) {
            'summary' => $this->formatSummary($items, $showCount),
            'compact' => $this->formatCompact($items, $separator, $maxItems, $showCount),
            'detailed' => $this->formatDetailed($items, $property, $regionContext),
            default => $this->formatList($items, $separator, $maxItems, $showCount, $property, $regionContext),
        };
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value instanceof CollectionInterface) {
            return $value->isEmpty();
        }
        if (is_array($value)) {
            return empty($value);
        }
        return true;
    }

    private function formatList(
        array $items,
        string $separator,
        ?int $maxItems,
        bool $showCount,
        ?ReflectionProperty $property,
        ?RegionContextInterface $regionContext,
    ): string {
        // Format each item
        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = $this->presenterFactory->displayValue($item, $property, $regionContext);
        }

        // Limit items if specified
        if ($maxItems !== null && count($formattedItems) > $maxItems) {
            $visible = array_slice($formattedItems, 0, $maxItems);
            $remaining = count($formattedItems) - $maxItems;

            $result = implode($separator, $visible);
            $result .= $this->translator->translate('common.and_n_more', ['count' => $remaining]);

            if ($showCount) {
                $result = '(' . count($formattedItems) . ') ' . $result;
            }

            return $result;
        }

        $result = implode($separator, $formattedItems);

        if ($showCount) {
            $result = '(' . count($formattedItems) . ') ' . $result;
        }

        return $result;
    }

    private function formatSummary(array $items, bool $showCount): string
    {
        $count = count($items);

        if ($count === 0) {
            return $this->emptyText;
        }

        // Try to get the type of items
        $firstItem = reset($items);
        $itemType = get_class($firstItem);
        $typeName = $this->getTypeDisplayName($itemType);

        if ($showCount) {
            return sprintf('%d %s', $count, $typeName);
        }

        return $typeName;
    }

    private function formatCompact(
        array $items,
        string $separator,
        ?int $maxItems,
        bool $showCount,
    ): string {
        $count = count($items);

        if ($count === 0) {
            return $this->emptyText;
        }

        // Get compact representation of each item
        $compactItems = [];
        foreach ($items as $item) {
            $compactItems[] = $this->getCompactRepresentation($item);
        }

        // Limit if needed
        if ($maxItems !== null && $count > $maxItems) {
            $visible = array_slice($compactItems, 0, $maxItems);
            $remaining = $count - $maxItems;

            $result = implode($separator, $visible);
            $result .= ' +' . $remaining;
        } else {
            $result = implode($separator, $compactItems);
        }

        if ($showCount) {
            $result = '(' . $count . ') ' . $result;
        }

        return $result;
    }

    private function formatDetailed(array $items, ?ReflectionProperty $property, ?RegionContextInterface $regionContext): array
    {
        // Return as array with detailed information
        $result = [];

        foreach ($items as $index => $item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                $result[$index] = $item->toArray();
            } else {
                $result[$index] = $this->presenterFactory->displayValue($item, $property, $regionContext);
            }
        }

        return $result;
    }

    private function getCompactRepresentation(object $item): string
    {
        // Try various methods to get a compact representation
        $methods = ['__toString', 'getName', 'getTitle', 'getLabel', 'getCode', 'getId', 'getSku'];

        foreach ($methods as $method) {
            if (method_exists($item, $method)) {
                $value = $item->$method();
                if ($value !== null && $value !== '') {
                    $str = (string) $value;
                    if (strlen($str) > 15) {
                        return substr($str, 0, 12) . '...';
                    }
                    return $str;
                }
            }
        }

        // Fallback to class abbreviation
        $className = get_class($item);
        $parts = explode('\\', $className);
        $shortName = end($parts);

        if (strlen($shortName) > 10) {
            return substr($shortName, 0, 7) . '...';
        }

        return $shortName;
    }

    private function getTypeDisplayName(string $className): string
    {
        $parts = explode('\\', $className);
        $shortName = end($parts);

        // Convert PascalCase to readable format
        $readable = preg_replace('/(?<!^)([A-Z])/', ' $1', $shortName);

        // Pluralize if needed
        if (str_ends_with($readable, 's')) {
            return $readable;
        }

        return $readable . 's';
    }
}