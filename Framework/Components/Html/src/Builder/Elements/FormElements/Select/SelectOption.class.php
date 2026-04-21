<?php

declare(strict_types=1);

class SelectOption extends AbstractHtmlComponent
{
    private const string TAG = 'option';

    // ── Option-Specific Properties ──────────────────────────────────
    private bool $selected = false;
    private string $key;

    public function __construct(string $key, mixed $content)
    {
        $this->key = $key;
        $this->content = $content;
    }

    public function generate(): string
    {
        if ($this->hasDefaultValue() && $this->getDefaultValue() === $this->getContent()) {
            $this->selected = true;
        }

        $option = [];
        $option[] = $this->getTagAttributes(
            array_merge(['value' => $this->key], get_object_vars($this)),
            self::TAG,
        );
        $option[] = $this->content ?? '';
        $option[] = '</option>';

        return implode('', $option);
    }

    // ── Option-Specific Setters ─────────────────────────────────────

    public function selected(bool $selected = true): static
    {
        $this->selected = $selected;
        return $this;
    }

    public function key(string $key): static
    {
        $this->key = $key;
        return $this;
    }

    /**
     * For SelectOption, value maps to content (display text).
     * The key is what gets submitted as the form value.
     */
    #[Override]
    public function value(mixed $value): static
    {
        $this->content = $value;
        return $this;
    }
}