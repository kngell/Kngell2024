<?php

declare(strict_types=1);

abstract class AbstractInput extends AbstractFormDataElement
{
    // private const array ATTR_VALUES_TYPE = ['string', 'bool', 'boolean', 'array', 'integer'];
    private const string TAG = 'input';
    protected bool $readonly;
    protected bool $disabled;
    protected int $size;
    protected int $maxlength;
    protected mixed $min;
    protected mixed $max;
    protected bool $multiple;
    protected string $pattern;
    protected mixed $placeholder;
    protected bool $required;
    protected int $step;
    protected bool $autofocus;
    protected int $height;
    protected int $width;
    protected string $list;
    protected string $autocomplete;

    public function getFormElementAttributes(string $type) : string
    {
        $strErrors = $this->inputErrors($this->name);
        $this->value = $this->inputValue($this->name, $this->value ?? '');
        $input = $this->getTagAttributes(
            array_merge(['type' => $type], get_object_vars($this)),
            self::TAG
        );
        return $input . $strErrors;
    }

    /**
     * @param bool $readonly
     * @return AbstractInput
     */
    public function readonly(bool $readonly): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * @param bool $disabled
     * @return AbstractInput
     */
    public function disabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * @param string $alt
     * @return AbstractInput
     */
    public function alt(string $alt) : self
    {
        $this->alt = $alt;
        return $this;
    }

    /**
     * @param string $src
     * @return AbstractInput
     */
    public function src(string $src) : self
    {
        $this->src = $src;
        return $this;
    }

    /**
     * @param bool $hidden
     * @return AbstractInput
     */
    public function hidden(bool $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * @param int $size
     * @return AbstractInput
     */
    public function size(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param int $maxlength
     * @return AbstractInput
     */
    public function maxlength(int $maxlength): self
    {
        $this->maxlength = $maxlength;
        return $this;
    }

    /**
     * @param string $accept
     * @return AbstractInput
     */
    public function accept(string $accept): self
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * @param mixed $min
     * @return AbstractInput
     */
    public function min(mixed $min): self
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @param mixed $max
     * @return AbstractInput
     */
    public function max(mixed $max): self
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @param bool $multiple
     * @return AbstractInput
     */
    public function multiple(bool $multiple): self
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @param string $pattern
     * @return AbstractInput
     */
    public function pattern(string $pattern): self
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @param mixed $placeholder
     * @return AbstractInput
     */
    public function placeholder(mixed $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param bool $required
     * @return AbstractInput
     */
    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param int $step
     * @return AbstractInput
     */
    public function step(int $step): self
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @param bool $autofocus
     * @return AbstractInput
     */
    public function autofocus(bool $autofocus = true): self
    {
        $this->autofocus = $autofocus;
        return $this;
    }

    /**
     * @param int $height
     * @return AbstractInput
     */
    public function height(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param int $width
     * @return AbstractInput
     */
    public function width(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $list
     * @return AbstractInput
     */
    public function list(string $list): self
    {
        $this->list = $list;
        return $this;
    }

    /**
     * @param string $autocomplete
     * @return AbstractInput
     */
    public function autocomplete(string $autocomplete): self
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    /**
     * @param string $id
     * @return AbstractInput
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string ...$class
     * @return AbstractInput
     */
    public function class(string ...$class): self
    {
        $this->class = array_merge($this->class, $class);
        return $this;
    }

    /**
     * @param string $name
     * @return AbstractInput
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $value
     * @return AbstractInput
     */
    public function value(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param int $tabindex
     * @return AbstractInput
     */
    public function tabindex(int $tabindex): self
    {
        $this->tabindex = $tabindex;
        return $this;
    }

    /**
     * @param string $title
     * @return AbstractInput
     */
    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param array $custom
     * @return HtmlBuilder
     */
    public function custom(array $custom): self
    {
        $this->custom = $custom;
        return $this;
    }
}