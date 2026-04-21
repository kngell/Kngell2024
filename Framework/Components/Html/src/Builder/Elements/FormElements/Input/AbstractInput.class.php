<?php

declare(strict_types=1);

abstract class AbstractInput extends AbstractHtmlComponent
{
    // Subclasses define this (e.g., 'text', 'checkbox', 'hidden')
    protected const string TYPE = '';
    private const string TAG = 'input';

    // ── Input-Specific Properties ───────────────────────────────────
    protected bool $readonly = false;
    protected int $size = 0;
    protected int $maxlength = 0;
    protected mixed $min = null;
    protected mixed $max = null;
    protected bool $multiple = false;
    protected string $pattern = '';
    protected int $step = 0;
    protected bool $autofocus = false;
    protected int $height = 0;
    protected int $width = 0;
    protected string $list = '';
    protected string $autocomplete = '';

    /**
     * Default generate for ALL input types.
     * Override only if a type needs custom rendering.
     */
    public function generate(): string
    {
        return $this->getFormElementAttributes(static::TYPE);
    }

    /**
     * Build the <input type="..."> tag with all attributes.
     */
    public function getFormElementAttributes(string $type): string
    {
        return $this->getTagAttributes(
            array_merge(['type' => $type], get_object_vars($this)),
            self::TAG,
        );
    }

    // ── Input-Specific Setters (unchanged) ──────────────────────────

    public function readonly(bool $readonly = true): static
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function size(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function maxlength(int $maxlength): static
    {
        $this->maxlength = $maxlength;
        return $this;
    }

    public function min(mixed $min): static
    {
        $this->min = $min;
        return $this;
    }

    public function max(mixed $max): static
    {
        $this->max = $max;
        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function pattern(string $pattern): static
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function step(int $step): static
    {
        $this->step = $step;
        return $this;
    }

    public function autofocus(bool $autofocus = true): static
    {
        $this->autofocus = $autofocus;
        return $this;
    }

    public function height(int $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function width(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function list(string $list): static
    {
        $this->list = $list;
        return $this;
    }

    public function autocomplete(string $autocomplete): static
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }
}