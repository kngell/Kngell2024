<?php

declare(strict_types=1);

class TextAreaElement extends AbstractHtmlComponent
{
    private const string TAG = 'textarea';

    // ── Textarea-Specific Properties ────────────────────────────────
    private int $rows;
    private int $cols;
    private string $autocapitalize;
    private string $autocomplete;
    private string $autocorrect;
    private string $dirname;
    private string $form;
    private int $maxlength;
    private int $minlength;
    private bool $autofocus;
    private bool $readonly;
    private string $wrap;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function generate(): string
    {
        $errorStr = $this->inputErrors($this->name);
        $textArea = $this->getTagAttributes(get_object_vars($this), self::TAG);
        $textArea .= $this->inputValue($this->name, $this->content ?? '');
        return $textArea . '</textarea>' . $errorStr;
    }

    // ── Textarea-Specific Setters ───────────────────────────────────

    public function rows(int $rows): static
    {
        $this->rows = $rows;
        return $this;
    }

    public function cols(int $cols): static
    {
        $this->cols = $cols;
        return $this;
    }

    public function autocapitalize(string $autocapitalize): static
    {
        $this->autocapitalize = $autocapitalize;
        return $this;
    }

    public function autocomplete(string $autocomplete): static
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    public function autocorrect(string $autocorrect): static
    {
        $this->autocorrect = $autocorrect;
        return $this;
    }

    public function dirname(string $dirname): static
    {
        $this->dirname = $dirname;
        return $this;
    }

    public function form(string $form): static
    {
        $this->form = $form;
        return $this;
    }

    public function maxlength(int $maxlength): static
    {
        $this->maxlength = $maxlength;
        return $this;
    }

    public function minlength(int $minlength): static
    {
        $this->minlength = $minlength;
        return $this;
    }

    public function autofocus(bool $autofocus = true): static
    {
        $this->autofocus = $autofocus;
        return $this;
    }

    public function readonly(bool $readonly = true): static
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function wrap(string $wrap): static
    {
        $this->wrap = $wrap;
        return $this;
    }
}