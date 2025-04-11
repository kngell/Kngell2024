<?php

declare(strict_types=1);

abstract class AbstractHtmlComponent
{
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
    protected ?string $content;
    protected string $src;
    protected string $alt;

    public function setParent(?self $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    public function add(self|null ...$htmlelements) : self
    {
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

    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    abstract public function generate(): string;

    protected function getTagAttributes(array $tagAttrs, string $tag) : string
    {
        $tag = '<' . $tag;
        foreach ($tagAttrs as $attr => $value) {
            if (in_array($attr, ['content', 'tag', 'formErrors', 'formValues', 'token', 'position']) || is_object($value)) {
                continue;
            }

            $tag .= $this->tagAttribute($attr, $value);
        }
        return $tag .= '>';
    }

    private function tagAttribute(string $key, string|array|bool|int $value) : string
    {
        if ($this instanceof CheckBoxType && $key === 'value' && $value === 'on') {
            return 'checked';
        }
        return match (true) {
            $key === 'action' => ' ' . $key . '="/' . $value . '"',
            $key === 'acceptCharset' => ' accept-charset="' . $value . '"',
            is_bool($value) => ' ' . $key,
            is_array($value) && $key === 'custom' => $this->customAttr($value),
            is_array($value) && $key === 'style' => " $key='" . implode('; ', $value) . "'",
            is_array($value) => " $key='" . implode(' ', $value) . "'",

            default => " $key='" . $value . "'",
        };
    }

    private function customAttr(array $Attrs) : string
    {
        $attrStr = '';
        foreach ($Attrs as $key => $attr) {
            $attrStr .= "$key ='" . $attr . "'";
        }
        return $attrStr;
    }
}