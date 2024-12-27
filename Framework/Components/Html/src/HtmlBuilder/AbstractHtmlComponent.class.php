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

    public function setParent(?self $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    public function add(self|AbstractForm|null ...$htmlelements) : self
    {
        return $this;
    }

    public function remove(self $component): self
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
}