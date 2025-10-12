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
    protected array $formErrors = [];
    protected array $formValues = [];
    protected string $errorMessage = '';
    protected string $htmlBlock;
    protected ?string $content;
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

    public function htmlBlock(string $htmlBlock = ''): HtmlBlockElement
    {
        return new HtmlBlockElement($htmlBlock);
    }

    public function hasErrorMessage(): bool
    {
        return !empty($this->errorMessage);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
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

    public function hasValue(): bool
    {
        return isset($this->value);
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

    protected function inputValue(string $name, mixed $value): mixed
    {
        $value = $value;
        if (isset($this->formValues) && array_key_exists($name, $this->formValues)) {
            $value = $this->imputVal($name);
        }
        return $value;
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
        $html = '<' . $tag;

        foreach ($tagAttrs as $attr => $value) {
            if (
                in_array($attr, ['content', 'contentUp', 'tag', 'formErrors', 'formValues', 'token', 'position', 'htmlBlock', 'errorMessage', 'level'], true)
                || is_object($value)
            ) {
                continue;
            }

            $html .= $this->tagAttribute($attr, $value);
        }

        // void tags are closed directly
        if (in_array(strtolower($tag), self::VOID_TAGS, true)) {
            return $html . ' />';
        }

        return $html . '>';
    }

    protected function populateField(): void
    {
        $strErrors = '';
        if (isset($this->name)) {
            $strErrors = $this->inputErrors($this->name);
            $this->value = $this->inputValue($this->name, $this->value ?? '');
            if ($this instanceof SelectElement && $this->value) {
                foreach ($this->children as $child) {
                    $child->value($this->value);
                }
            }
        }
        $this->errorMessage = $strErrors;
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
        return match (true) {
            $this instanceof CheckBoxType && $key === 'value' && $value === 'on' => 'checked',

            $this instanceof SelectOption && in_array($key, ['disabled', 'selected']) => $key,

            // Form action URL
            $key === 'action' => ' ' . $key . '="/' . $value . '"',

            // Boolean attributes
            is_bool($value) => $value ? ' ' . $key : '',

            // Custom attributes handled by separate method
            is_array($value) && $key === 'custom' => $this->customAttr($value),

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