<?php

declare(strict_types=1);

abstract class AbstractHtmlComponent
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

    protected int $level = 0;
    protected AbstractHtmlComponent|null $parent;
    protected string $accesskey;
    protected array $class = [];
    protected string $contenteditable;
    protected string $data;
    protected string $dir;
    protected string $draggable;
    protected string $enterkeyhint;
    protected bool $hidden;
    protected string $id;
    protected string $inert;
    protected string $inputmode;
    protected string $lang;
    protected string $popover;
    protected string $spellcheck;
    protected array $style;
    protected int $tabindex;
    protected string $title;
    protected string $translate;
    protected array $custom;
    protected string $align;
    protected string $accept;
    protected string $onclick;
    protected string $ondblclick;
    protected string $onmousedown;
    protected string $onmouseup;
    protected string $onmouseover;
    protected string $onmousemove;
    protected string $onmouseout;
    protected string $onkeypress;
    protected string $onkeydown;
    protected string $onkeyup;
    protected string $onchange;
    protected array $formErrors = [];
    protected array $formValues = [];
    protected string $errorMessage = '';
    protected string $htmlBlock;
    protected null|string|int $content;
    protected string|null $src;
    protected string $alt;
    protected string $href;
    protected bool $contentUp = true;
    protected string $role;
    protected string $name;
    protected mixed $value;
    protected string $dataBsToggle;
    protected string $ariaLabel;
    protected bool $ariaHaspopup;
    protected bool $ariaExpanded;
    protected array $aria = [];
    protected string $defaultValue;
    protected bool $controls;
    protected string $text;
    protected bool $ariaHidden;
    protected bool $disabled;

    public function setParent(?self $parent)
    {
        $this->parent = $parent;
        $this->level = $parent ? $parent->getLevel() + 1 : 0;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function add(self|null ...$htmlelements): self
    {
        $h = $htmlelements;
        return $this;
    }

    /**
     * @param array $custom
     *
     * @return AbstractHtmlComponent
     */
    public function custom(array $custom): self
    {
        $this->custom = $custom;
        return $this;
    }

    public function aria(string $name, string ...$props): self
    {
        $aria = [];
        foreach ($props as $prop) {
            $aria['aria-' . $name] = $prop;
        }
        $this->aria = array_merge($this->aria, $aria);
        return $this;
    }

    public function remove(self $component): self
    {
        return $this;
    }

    public function formErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    public function errorMessage(string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function htmlBlock(?string $htmlBlock = null): HtmlBlockElement
    {
        if ($htmlBlock === null) {
            $htmlBlock = '';
        }
        return new HtmlBlockElement($htmlBlock);
    }

    public function text(?string $text = null): HtmlTextElement
    {
        if ($text === null) {
            $text = '';
        }
        return new HtmlTextElement($text);
    }

    public function hasErrorMessage(): bool
    {
        return !empty($this->errorMessage);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function hasDefaultValue(): bool
    {
        return isset($this->defaultValue);
    }

    public function defaultValue(string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    abstract public function generate(): string;

    /**
     * @param string ...$class
     *
     * @return AbstractHtmlComponent
     */
    public function class(string ...$class): self
    {
        $this->class = array_merge($this->class, $class);
        return $this;
    }

    /**
     * @param string $ariaLabel
     *
     * @return AbstractHtmlComponent
     */
    public function ariaLabel(string $ariaLabel): self
    {
        $this->ariaLabel = $ariaLabel;
        return $this;
    }

    /**
     * @param bool $ariaHidden
     *
     * @return AbstractHtmlComponent
     */
    public function ariaHidden(bool $ariaHidden = true): self
    {
        $this->ariaHidden = $ariaHidden;
        return $this;
    }

    public function getClass(): array
    {
        return $this->class;
    }

    public function hasInputBoxContainer(): bool
    {
        foreach ($this->class as $class) {
            $parts = explode(' ', $class);
            return $parts[0] === 'input-box' ? true : false;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     *
     * @return AbstractHtmlComponent
     */
    public function value(mixed $value): AbstractHtmlComponent
    {
        $this->value = $value;

        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function href(string $href): self
    {
        $this->href = $href;
        return $this;
    }

    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    protected function inputErrors(string $name, string $type = ''): string
    {
        $errorStr = '';
        $isError = false;
        if (isset($this->formErrors) && array_key_exists($name, $this->formErrors)) {
            foreach ($this->formErrors[$name] as $error) {
                $errorStr .= $error;
            }
            $isError = true;
        }
        $this->inputClassStyle($type, $isError);
        return $errorStr;
    }

    // protected function inputValue(string $name, mixed $value): mixed
    // {
    //     $value = $value;
    //     if (isset($this->formValues) && array_key_exists($name, $this->formValues)) {
    //         $value = $this->imputVal($name);
    //     }
    //     return $value;
    // }

    protected function inputValue(string $name, mixed $default = null): mixed
    {
        if (!isset($this->formValues) || !is_array($this->formValues)) {
            return $default;
        }

        if (str_contains($name, '.')) {
            return $this->getNestedValue($name, $default);
        }

        return array_key_exists($name, $this->formValues) ? $this->imputVal($name) : $default;
    }

    protected function inputClassStyle(string $type = '', bool $isError = false): void
    {
        if ($isError) {
            isset($this->class) ? array_push($this->class, 'is-invalid') : $this->class = ['is-invalid'];
        } else {
            if (isset($this->class)) {
                foreach ($this->class as $key => $class) {
                    if ($class === 'is-invalid') {
                        unset($this->class[$key]);
                    }
                }
                $this->class[] = $this->isValidClass($type);
            }
        }
    }

    protected function getTagAttributes(array $tagAttrs, string $tag): string
    {
        $attributes = [];
        $attributes[] = '<' . $tag;

        foreach ($tagAttrs as $attr => $value) {
            if (
                in_array($attr, ['content', 'contentUp', 'tag', 'formErrors', 'formValues', 'token', 'position', 'htmlBlock', 'errorMessage', 'level', 'key', 'includeToken'], true)
                || is_object($value)
            ) {
                continue;
            }
            $attribute = $this->tagAttribute($attr, $value);
            !empty($attribute) ? $attributes[] = $attribute : '';
        }

        // void tags are closed directly
        if (in_array(strtolower($tag), self::VOID_TAGS, true)) {
            $attributes[] = ' />';
            return implode('', $attributes);
        }
        $attributes[] = '>';
        return implode('', $attributes);
    }

    protected function populateField(): void
    {
        $strErrors = '';
        if (isset($this->name)) {
            $strErrors = $this->inputErrors($this->name);
            $this->value = $this->inputValue($this->name, $this->value ?? '');
            if ($this instanceof SelectElement && $this->value) {
                foreach ($this->children as $child) {
                    $child->defaultValue($this->value);
                }
            }
        }

        $this->errorMessage = $strErrors;
    }

    private function getNestedValue(string $path, mixed $default = null): mixed
    {
        $keys = explode('.', $path);
        $value = $this->formValues;

        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }

    private function imputVal(string $name): mixed
    {
        if (!str_contains($name, 'password')) {
            if (is_string($this->formValues[$name]) && DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->formValues[$name])) {
                $date = new DateTimeImmutable($this->formValues[$name]);
                return $date->format('Y-m-d');
            }
            return $this->formValues[$name];
        }
        return '';
    }

    private function isValidClass(string $type = ''): string
    {
        if (isset($this->formValues[$this->name]) && !str_contains($this->name, 'password')) {
            if (isset($type) || $type !== 'submit') {
                return 'is-valid';
            }
        }
        return '';
    }

    private function tagAttribute(string $key, string|array|bool|int|null $value): string
    {
        $type = gettype($value);
        return match (true) {
            $this instanceof CheckBoxType && $key === 'value' && $value === 'on' => 'checked',

            // $this instanceof SelectOption && in_array($key, ['disabled', 'selected']) => ' ' . $key,

            // Form action URL
            $key === 'action' => ' ' . $key . '="/' . $value . '"',

            // Boolean attributes
            $type === 'boolean' => $value === true ? (string) (' ' . $key) : '',

            // Custom attributes handled by separate method
            is_array($value) && in_array($key, ['custom', 'aria']) => $this->customAttr($value),

            // Style array
            is_array($value) && $key === 'style' =>
                $this->arrayNotEmpty($value)
                    ? " $key='" . implode('; ', array_filter($value, fn ($v) => !empty($v) || $v === '0')) . "'"
                    : '',

            // Other array attributes (like class)
            is_array($value) =>
                $this->arrayNotEmpty($value)
                    ? " $key='" . trim(implode($this->separator($key, $value), array_filter($value, fn ($v) => !empty($v) || $v === '0'))) . "'"
                    : '',
            !is_array($value) && empty($value) && $this instanceof SelectOption => ' ' . $key . "='" . $value . "'",

            // Default: string/int values, including '0'
            default =>
                (!empty($value) || $value === '0')
                    ? ' ' . StringUtils::camelCaseToKebabCase($key) . "='" . $value . "'"
                    : '',
        };
    }

    private function separator(string $key, array $value): string
    {
        if ($key === 'class') {
            // Remove empty/null/false values before counting
            $filtered = array_filter($value, fn ($v) => !empty($v) || $v === '0');
            return count($filtered) > 1 ? ' ' : '';
        }

        // Default separator for other array attributes
        return self::SEPARATORS[$key] ?? ' ';
    }

    /**
     * Checks if an array is effectively non-empty (ignores empty strings, nulls, false).
     */
    private function arrayNotEmpty(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }
        foreach ($arr as $v) {
            if (is_array($v)) {
                if ($this->arrayNotEmpty($v)) {
                    return true;
                }
            } elseif (!empty($v) || $v === '0') {
                return true;
            }
        }
        return false;
    }

    private function customAttr(array $Attrs): string
    {
        $attrStr = '';
        foreach ($Attrs as $key => $attr) {
            $key = StringUtils::camelCaseToKebabCase($key);
            $attrStr .= !empty($attr) ? "$key ='" . $attr . "'" : '';
        }
        return $attrStr;
    }
}