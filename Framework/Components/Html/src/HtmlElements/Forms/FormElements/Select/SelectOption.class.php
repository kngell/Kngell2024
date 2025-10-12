<?php

declare(strict_types=1);
class SelectOption extends AbstractHtmlComponent
{
    private bool $disabled;
    private bool $selected;
    private string $key;

    public function __construct(string $key, string $content)
    {
        $this->key = $key;
        $this->content = $content;
    }

    public function generate(): string
    {
        $option = '<option  value=' . $this->key;
        if ($this->hasValue() && $this->getValue() === $this->key) {
            $option .= ' Selected';
        }
        $option .= '>' . $this->content;
        return $option . '</option>';
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function selected(bool $selected = true): self
    {
        $this->selected = $selected;
        return $this;
    }
}