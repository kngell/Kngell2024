<?php

declare(strict_types=1);

trait TagRendererTrait
{
    protected const array VOID_TAGS = [
        'area', 'base', 'br', 'col', 'embed', 'hr',
        'img', 'input', 'link', 'meta', 'param',
        'source', 'track', 'wbr',
    ];

    private const array SEPARATORS = [
        'class' => ' ',
        'style' => '; ',
        'data-tags' => ',',
    ];

    /**
     * Get HTML tag with attributes.
     */
    protected function getTagAttributes(array $tagAttrs, string $tag): string
    {
        if (empty($tag)) {
            return '';
        }

        $attributes = ['<' . $tag];

        // Skip system/internal attributes and objects
        $skipKeys = [
            'content', 'contentUp', 'tag', 'formErrors', 'formValues',
            'token', 'position', 'htmlBlock', 'errorMessage', 'level',
            'key', 'includeToken', 'defaultValue', 'children', 'parent',
        ];

        foreach ($tagAttrs as $attr => $value) {
            if (in_array($attr, $skipKeys, true) || is_object($value)) {
                continue;
            }

            $attribute = $this->tagAttribute($attr, $value);
            if (!empty($attribute)) {
                $attributes[] = $attribute;
            }
        }

        $attributes[] = in_array(strtolower($tag), self::VOID_TAGS, true) ? ' />' : '>';
        return implode('', $attributes);
    }

    /**
     * Build a single tag attribute
     * Delegates to TagAttributeHelper for consistent building.
     */
    private function tagAttribute(string $key, string|array|bool|int|null $value): string
    {
        $type = gettype($value);

        return match (true) {
            // Action attribute - always render
            $key === 'action' => ' ' . $key . '="' . $value . '"',

            // Boolean attributes (disabled, readonly, required, etc.)
            $type === 'boolean' => $value === true ? ' ' . $key : '',

            // Style attribute - uses helper
            is_array($value) && $key === 'style' => TagAttributeHelper::build($key, $value),

            // Custom attributes (data-*, aria-*) - uses helper
            is_array($value) && in_array($key, ['custom', 'aria']) => TagAttributeHelper::buildCustom($value),

            // Other array attributes (class, etc.) - uses helper with separator
            is_array($value) => $this->buildArrayAttribute($key, $value),

            // Special case for SelectOption - allow empty values
            !is_array($value) && empty($value) && $this instanceof SelectOption => ' ' . $key . "='" . $value . "'",

            // Default - use helper
            default => TagAttributeHelper::build($key, $value),
        };
    }

    /**
     * Build array attribute with custom separator handling
     * Only needed for special cases like class with multiple values.
     */
    private function buildArrayAttribute(string $key, array $value): string
    {
        if (!$this->arrayNotEmpty($value)) {
            return '';
        }

        // Use ArrayUtils::flatten
        $flattened = ArrayUtils::flatten($value);

        // Filter empty values (preserving '0')
        $filtered = array_filter($flattened, function ($v) {
            return $v !== null && $v !== '' && $v !== false;
        });

        if (empty($filtered)) {
            return '';
        }

        $separator = $this->getSeparator($key, $filtered);
        $attributeValue = trim(implode($separator, $filtered));

        return ' ' . $key . "='" . $attributeValue . "'";
    }

    /**
     * Get separator for array attribute values.
     */
    private function getSeparator(string $key, array $values): string
    {
        // For class with multiple values, use space
        if ($key === 'class' && count($values) > 1) {
            return ' ';
        }
        return self::SEPARATORS[$key] ?? ' ';
    }

    /**
     * Check if array has any non-empty values
     * Preserves '0' values.
     */
    private function arrayNotEmpty(array $arr): bool
    {
        foreach ($arr as $v) {
            if (is_array($v) && $this->arrayNotEmpty($v)) {
                return true;
            }
            // '0' is considered not empty
            if ($v !== null && $v !== '' && $v !== false) {
                return true;
            }
        }
        return false;
    }
}