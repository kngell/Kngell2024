<?php

declare(strict_types=1);

class TextAreaElement extends AbstractFormDataElement
{
    private const string TAG = 'textarea';
    private int $rows;
    private int $cols;
    private string $autocapitalize;
    private string $autocomplete;
    private string $autocorrect;
    private string $dirname;
    private string $form;
    private string $placeholder;
    private int $maxlength;
    private int $minlength;
    private bool $autofocus;
    private bool $disabled;
    private bool $readonly;
    private bool $required;
    private string $wrap;

    public function generate(): string
    {
        $errorStr = $this->inputErrors($this->name);
        $textArea = $this->getTagAttributes(get_object_vars($this), self::TAG);
        $textArea .= $this->inputValue($this->name, $this->content ?? '');
        return $textArea . '</textarea>' . $errorStr;
    }

    /**
     * @param int $rows
     * @return TextAreaElement
     */
    public function rows(int $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @param int $cols
     * @return TextAreaElement
     */
    public function cols(int $cols): self
    {
        $this->cols = $cols;
        return $this;
    }

    /**
     * @param string $autocapitalize
     * @return TextAreaElement
     */
    public function autocapitalize(string $autocapitalize): self
    {
        $this->autocapitalize = $autocapitalize;
        return $this;
    }

    /**
     * @param string $autocomplete
     * @return TextAreaElement
     */
    public function autocomplete(string $autocomplete): self
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    /**
     * @param string $autocorrect
     * @return TextAreaElement
     */
    public function autocorrect(string $autocorrect): self
    {
        $this->autocorrect = $autocorrect;
        return $this;
    }

    /**
     * @param string $dirname
     * @return TextAreaElement
     */
    public function dirname(string $dirname): self
    {
        $this->dirname = $dirname;
        return $this;
    }

    /**
     * @param string $form
     * @return TextAreaElement
     */
    public function form(string $form): self
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param string $placeholder
     * @return TextAreaElement
     */
    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param int $maxlength
     * @return TextAreaElement
     */
    public function maxlength(int $maxlength): self
    {
        $this->maxlength = $maxlength;
        return $this;
    }

    /**
     * @param int $minlength
     * @return TextAreaElement
     */
    public function minlength(int $minlength): self
    {
        $this->minlength = $minlength;
        return $this;
    }

    /**
     * @param bool $autofocus
     * @return TextAreaElement
     */
    public function autofocus(bool $autofocus): self
    {
        $this->autofocus = $autofocus;
        return $this;
    }

    /**
     * @param bool $disabled
     * @return TextAreaElement
     */
    public function disabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * @param bool $readonly
     * @return TextAreaElement
     */
    public function readonly(bool $readonly): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * @param bool $required
     * @return TextAreaElement
     */
    public function required(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param string $spellcheck
     * @return TextAreaElement
     */
    public function spellcheck(string $spellcheck): self
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    /**
     * @param string $wrap
     * @return TextAreaElement
     */
    public function wrap(string $wrap): self
    {
        $this->wrap = $wrap;
        return $this;
    }

    /**
     * @param string $id
     * @return TextAreaElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string ...$class
     * @return TextAreaElement
     */
    public function class(string ...$class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $name
     * @return TextAreaElement
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $value
     * @return TextAreaElement
     */
    public function value(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }
}