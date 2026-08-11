<?php

declare(strict_types=1);

/**
 * HTML Tag Attribute Helper.
 *
 * Builds HTML tag attributes with proper handling of:
 * - Boolean attributes (disabled, readonly, checked)
 * - Array attributes (class, style, data-*)
 * - Scalar values including '0' (not treated as empty)
 * - Reuses StringUtils for camelCase to kebab-case conversion
 * - Reuses ArrayUtils for array flattening
 */
final readonly class TagAttributeHelper
{
    private function __construct()
    {
        // Prevent instantiation
    }

    public static function build(string $key, string|array|bool|int|null $value, bool $kebab = true): string
    {
        // Handle null values
        if ($value === null) {
            return '';
        }

        // Handle boolean attributes
        if (is_bool($value)) {
            return $value ? ' ' . $key : '';
        }

        // Handle arrays
        if (is_array($value)) {
            return self::buildArrayAttribute($key, $value, $kebab);
        }

        // Handle scalar values
        return self::buildScalarAttribute($key, $value, $kebab);
    }

    public static function buildCustom(array $attrs, bool $kebab = true): string
    {
        $attrStr = '';

        foreach ($attrs as $key => $attr) {
            // Skip null, empty string, false
            if ($attr === null || $attr === '' || $attr === false) {
                continue;
            }

            // Convert camelCase to kebab-case
            $key = $kebab ? StringUtils::camelCaseToKebabCase($key) : $key;

            if ($attr === true) {
                $attrStr .= ' ' . $key;
            } else {
                $attrStr .= is_array($attr)
                    ? ' ' . $key . "='" . implode(' ', ArrayUtils::flatten($attr)) . "'"
                    : ' ' . $key . "='" . $attr . "'";
            }
        }

        return $attrStr;
    }

    private static function buildArrayAttribute(string $key, array $values, bool $kebab): string
    {
        // Special handling for style attribute
        if ($key === 'style') {
            return self::buildStyleAttribute($values);
        }

        // Use ArrayUtils::flatten for nested arrays
        $flattened = ArrayUtils::flatten($values);

        // Filter empty values (preserving '0')
        $filtered = array_filter($flattened, function ($value) {
            return $value !== null && $value !== '' && $value !== false;
        });

        if (empty($filtered)) {
            return '';
        }

        $key = self::formatKey($key, $kebab);
        $value = implode(' ', $filtered);

        return ' ' . $key . '="' . $value . '"';
    }

    private static function buildStyleAttribute(array $styles): string
    {
        $styleString = '';

        foreach ($styles as $property => $value) {
            if ($value === null || $value === '' || $value === false) {
                continue;
            }

            // Use StringUtils for conversion
            $property = StringUtils::camelCaseToKebabCase($property);
            $styleString .= $property . ': ' . $value . '; ';
        }

        if (empty($styleString)) {
            return '';
        }

        return ' style="' . trim($styleString) . '"';
    }

    private static function buildScalarAttribute(string $key, mixed $value, bool $kebab): string
    {
        if ($value === '' || $value === false) {
            return '';
        }

        $key = self::formatKey($key, $kebab);

        return ' ' . $key . '="' . $value . '"';
    }

    private static function formatKey(string $key, bool $kebab): string
    {
        // Skip formatting for special keys
        if (in_array($key, ['aria', 'data', 'custom', 'style', 'class'])) {
            return $key;
        }

        return $kebab ? StringUtils::camelCaseToKebabCase($key) : $key;
    }
}