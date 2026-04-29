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

    protected function getTagAttributes(array $tagAttrs, string $tag): string
    {
        $attributes = ['<' . $tag];

        foreach ($tagAttrs as $attr => $value) {
            if (
                in_array($attr, [
                    'content', 'contentUp', 'tag', 'formErrors', 'formValues',
                    'token', 'position', 'htmlBlock', 'errorMessage', 'level',
                    'key', 'includeToken', 'defaultValue', 'children', 'parent',
                ], true)
                || is_object($value)
            ) {
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

    private function tagAttribute(string $key, string|array|bool|int|null $value): string
    {
        $type = gettype($value);
        return match (true) {
            $this instanceof CheckBoxType && $key === 'value' && $value === 'on' => 'checked',
            $key === 'action' => ' ' . $key . '="' . $value . '"',
            $type === 'boolean' => $value === true ? ' ' . $key : '',
            is_array($value) && $key === 'style' => $this->buildStyleAttribute($value),
            is_array($value) && in_array($key, ['custom', 'aria']) => $this->customAttr($value),
            is_array($value) => $this->buildArrayAttribute($key, $value),
            !is_array($value) && empty($value) && $this instanceof SelectOption => ' ' . $key . "='" . $value . "'",
            default => (!empty($value) || $value === '0')
                ? ' ' . StringUtils::camelCaseToKebabCase($key) . "='" . $value . "'"
                : '',
        };
    }

    private function buildStyleAttribute(array $value): string
    {
        if (!$this->arrayNotEmpty($value)) {
            return '';
        }
        return " style='" . implode('; ', array_map(
            fn ($k, $v) => "$k: $v",
            array_keys($value),
            array_filter($value, fn ($v) => $v !== null && $v !== ''),
        )) . "'";
    }

    private function buildArrayAttribute(string $key, array $value): string
    {
        if (!$this->arrayNotEmpty($value)) {
            return '';
        }
        $filtered = array_filter($value, fn ($v) => !empty($v) || $v === '0');
        return " $key='" . trim(implode($this->separator($key, $value), $filtered)) . "'";
    }

    private function separator(string $key, array $value): string
    {
        if ($key === 'class') {
            $filtered = array_filter($value, fn ($v) => !empty($v) || $v === '0');
            return count($filtered) > 1 ? ' ' : '';
        }
        return self::SEPARATORS[$key] ?? ' ';
    }

    private function arrayNotEmpty(array $arr): bool
    {
        foreach ($arr as $v) {
            if (is_array($v) && $this->arrayNotEmpty($v)) {
                return true;
            }
            if (!empty($v) || $v === '0') {
                return true;
            }
        }
        return false;
    }

    private function customAttr(array $attrs): string
    {
        $attrStr = '';
        foreach ($attrs as $key => $attr) {
            $key = StringUtils::camelCaseToKebabCase($key);
            $attrStr .= !empty($attr) ? " $key='" . $attr . "'" : '';
        }
        return $attrStr;
    }
}