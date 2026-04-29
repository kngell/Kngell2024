<?php

declare(strict_types=1);

trait HtmlAttributesTrait
{
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
    protected array $style = [];
    protected int $tabindex;
    protected string $title;
    protected string $translate;
    protected string $align;
    protected string $href;
    protected string|null $src;
    protected string $alt;
    protected string $script;
    protected string $text;
    protected bool $controls;
    protected string $dataBsToggle;
    protected array $custom = [];

    public function accesskey(string $accesskey): static
    {
        $this->accesskey = $accesskey;
        return $this;
    }

    public function contenteditable(string $contenteditable): static
    {
        $this->contenteditable = $contenteditable;
        return $this;
    }

    public function data(string $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function hasInputBoxContainer(): bool
    {
        foreach ($this->class as $className) {
            if (str_starts_with(trim($className), 'input-box')) {
                return true;
            }
        }
        return false;
    }

    public function dir(string $dir): static
    {
        $this->dir = $dir;
        return $this;
    }

    public function draggable(string $draggable): static
    {
        $this->draggable = $draggable;
        return $this;
    }

    public function enterkeyhint(string $enterkeyhint): static
    {
        $this->enterkeyhint = $enterkeyhint;
        return $this;
    }

    public function hidden(bool $hidden = true): static
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function id(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function inert(string $inert): static
    {
        $this->inert = $inert;
        return $this;
    }

    public function inputmode(string $inputmode): static
    {
        $this->inputmode = $inputmode;
        return $this;
    }

    public function lang(string $lang): static
    {
        $this->lang = $lang;
        return $this;
    }

    public function popover(string $popover): static
    {
        $this->popover = $popover;
        return $this;
    }

    public function spellcheck(string $spellcheck): static
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    public function tabindex(int $tabindex): static
    {
        $this->tabindex = $tabindex;
        return $this;
    }

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function translate(string $translate): static
    {
        $this->translate = $translate;
        return $this;
    }

    public function align(string $align): static
    {
        $this->align = $align;
        return $this;
    }

    public function href(string $href): static
    {
        $this->href = $href;
        return $this;
    }

    public function src(string $src): static
    {
        $this->src = $src;
        return $this;
    }

    public function alt(string $alt): static
    {
        $this->alt = $alt;
        return $this;
    }

    public function script(string $script): static
    {
        $this->script = $script;
        return $this;
    }

    public function controls(bool $controls = true): static
    {
        $this->controls = $controls;
        return $this;
    }

    public function dataBsToggle(string $dataBsToggle): static
    {
        $this->dataBsToggle = $dataBsToggle;
        return $this;
    }

    public function class(string ...$class): static
    {
        $this->class = array_merge($this->class, $class);
        return $this;
    }

    public function removeClass(string $class): static
    {
        $key = array_search($class, $this->class, true);
        if ($key !== false) {
            unset($this->class[$key]);
            $this->class = array_values($this->class);
        }
        return $this;
    }

    public function getClass(): array
    {
        return $this->class;
    }

    public function style(array $style): static
    {
        $this->style = $style;
        return $this;
    }

    public function custom(array $custom): static
    {
        $this->custom = array_merge($this->custom, $custom);
        return $this;
    }

    public function attribute(string|int ...$customAttr): static
    {
        $attrs = [];
        if (ArrayUtils::isKeyValueList($customAttr)) {
            $attrs = ArrayUtils::fromSequentialToAssoc($customAttr);
        }
        $this->custom = array_merge($this->custom, $attrs);
        return $this;
    }
}