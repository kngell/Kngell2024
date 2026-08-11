<?php

declare(strict_types=1);

abstract class AbstractInput extends AbstractHtmlComponent
{
    protected const string TYPE = '';
    private const string TAG = 'input';

    // ── Input-Specific Properties ───────────────────────────────────
    protected bool $readonly;
    protected int $size;
    protected int $maxlength;
    protected mixed $min;
    protected mixed $max;
    protected bool $multiple;
    protected string $pattern;
    protected int $step;
    protected bool $autofocus;
    protected int $height;
    protected int $width;
    protected string $list;
    protected string $autocomplete;
    protected string $type;

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

    /**
     * @param string $type
     *
     * @return AbstractInput
     */
    public function type(string $type): AbstractInput
    {
        $this->type = $type;
        return $this;
    }
}