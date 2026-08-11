<?php

declare(strict_types=1);

final class FieldErrorHtml
{
    private const DEFAULT_TAG = 'small';

    /**
     * Format an error message as HTML.
     *
     * @param string $errMsg The error message (already formatted by ValidationConfig)
     * @param array $classes CSS classes (already coming from ValidationConfig)
     * @param string $tag HTML tag to use
     * @param array $attributes Additional HTML attributes
     *
     * @return string
     */
    public static function format(
        string $errMsg,
        array $classes = [],
        string $tag = self::DEFAULT_TAG,
        array $attributes = [],
    ): string {
        if ($errMsg === '') {
            return '';
        }

        $classString = !empty($classes)
            ? ' class="' . htmlspecialchars(implode(' ', $classes)) . '"'
            : '';

        $attrString = self::buildAttributes($attributes);
        $escapedMsg = htmlspecialchars($errMsg, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return "<{$tag}{$classString}{$attrString}>{$escapedMsg}</{$tag}>";
    }

    /**
     * Format error for a specific field (adds accessibility attributes).
     */
    public static function forField(string $fieldName, string $errMsg, array $classes = []): string
    {
        return self::format(
            $errMsg,
            $classes,
            'small',
            [
                'id' => $fieldName . '-error',
                'role' => 'alert',
                'aria-live' => 'polite',
            ],
        );
    }

    /**
     * Format error as a block element (for form-level errors).
     */
    public static function asBlock(string $errMsg, array $classes = []): string
    {
        return self::format($errMsg, $classes, 'div', ['role' => 'alert']);
    }

    /**
     * Build HTML attributes string.
     */
    private static function buildAttributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $result = '';
        foreach ($attributes as $key => $value) {
            $result .= ' ' . htmlspecialchars((string) $key) . '="' . htmlspecialchars((string) $value) . '"';
        }
        return $result;
    }
}