<?php

declare(strict_types=1);

abstract class AbstractHtmlComponent
{
    protected AbstractHtmlComponent|null $parent;
    protected string $accesskey;
    protected array $class;
    protected string $contenteditable;
    protected string $data;
    protected string $dir;
    protected string $draggable;
    protected string $enterkeyhint;
    protected string $hidden;
    protected string $id;
    protected string $inert;
    protected string $inputmode;
    protected string $lang;
    protected string $popover;
    protected string $spellcheck;
    protected array $style;
    protected string $tabindex;
    protected string $title;
    protected string $translate;
    protected array $custom;
    protected string $align;
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
        return $this;
    }

    public function formValues(array $formValues): self
    {
        return $this;
    }

    abstract public function generate(): string;

    public function getTagAttribute(string $key, string|array $value) : string
    {
        return match (true) {
            is_string($value) => " $key='" . $value . "'",
            is_array($value) && $key === 'style' => " $key='" . implode('; ', $value) . "'",
            is_array($value) => " $key='" . implode(' ', $value) . "'",
        };
    }

    protected function formElementAttribute(string $key, string|array|bool|int $value) : string
    {
        if ($this instanceof CheckBoxType && $key === 'value' && $value === 'on') {
            return 'checked';
        }
        return match (true) {
            is_string($value) || is_int($value) => ' ' . $key . '="' . $value . '"',
            is_bool($value) => ' ' . $key,
            is_array($value) && $key === 'class' => ' class="' . implode(' ', $value) . '"',
            is_array($value) && $key === 'style' => ' style="' . implode('; ', $value) . '"',
            is_array($value) && $key === 'custom' => $this->customAttribute($value),
            default => ''
        };
    }

    private function customAttribute(array $Attrs) : string
    {
        $strCustom = '';
        foreach ($Attrs as $key => $attr) {
            $strCustom .= "$key ='" . $attr . "'";
        }
        return $strCustom;
    }
}