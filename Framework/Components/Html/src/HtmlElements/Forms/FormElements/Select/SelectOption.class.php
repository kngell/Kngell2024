<?php

declare(strict_types=1);
class SelectOption extends AbstractHtmlComponent
{
    private const string TAG = 'option';

    private bool $selected = false;
    private string $key;

    public function __construct(string $key, mixed $content)
    {
        $this->key = $key;
        $this->content = $content;
    }

    public function generate(): string
    {
        if ($this->hasDefaultValue() && $this->getDefaultValue() === $this->key) {
            $this->selected = true;
        }
        $option = [];
        $option[] = $this->getTagAttributes(array_merge(['value' => $this->key], get_object_vars($this)), self::TAG);

        // $this->selected ? $option .= ' Selected' : '';
        $option[] = $this->content ?? '';
        $option[] = '</option>';

        return implode('', $option);
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

    public function key(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function value(mixed $value): self
    {
        $this->content = $value;
        return $this;
    }
}