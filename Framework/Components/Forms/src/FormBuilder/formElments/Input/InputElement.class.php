<?php

declare(strict_types=1);

abstract class InputElement extends FormElement
{
    protected string $name;
    protected mixed $value;
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
    protected string $id;
    protected array $class;

    /**
     * @param string $name
     * @return InputElement
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $value
     * @return InputElement
     */
    public function value(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param bool $readonly
     * @return InputElement
     */
    public function readonly(bool $readonly): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * @param bool $disabled
     * @return InputElement
     */
    public function disabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * @param int $size
     * @return InputElement
     */
    public function size(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param int $maxlength
     * @return InputElement
     */
    public function maxlength(int $maxlength): self
    {
        $this->maxlength = $maxlength;
        return $this;
    }

    /**
     * @param mixed $min
     * @return InputElement
     */
    public function min(mixed $min): self
    {
        $this->min = $min;
        return $this;
    }

    /**
     * @param mixed $max
     * @return InputElement
     */
    public function max(mixed $max): self
    {
        $this->max = $max;
        return $this;
    }

    /**
     * @param bool $multiple
     * @return InputElement
     */
    public function multiple(bool $multiple): self
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @param string $pattern
     * @return InputElement
     */
    public function pattern(string $pattern): self
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * @param mixed $placeholder
     * @return InputElement
     */
    public function placeholder(mixed $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param bool $required
     * @return InputElement
     */
    public function required(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @param int $step
     * @return InputElement
     */
    public function step(int $step): self
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @param bool $autofocus
     * @return InputElement
     */
    public function autofocus(bool $autofocus): self
    {
        $this->autofocus = $autofocus;
        return $this;
    }

    /**
     * @param int $height
     * @return InputElement
     */
    public function height(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param int $width
     * @return InputElement
     */
    public function width(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param string $list
     * @return InputElement
     */
    public function list(string $list): self
    {
        $this->list = $list;
        return $this;
    }

    /**
     * @param string $autocomplete
     * @return InputElement
     */
    public function autocomplete(string $autocomplete): self
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    /**
     * @param string $id
     * @return InputElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array $class
     * @return InputElement
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    protected function getFormElementAttributes(string $type, array $props = []) : string
    {
        $input = "<input type='" . $type . "'";
        $errorStr = $this->inputErrors($this->name);
        $this->value = $this->inputValue($this->name, $this->value);
        foreach ($this->__toArray() as $key => $value) {
            if (in_array(gettype($value), ['string', 'bool', 'boolean', 'array', 'integer']) && $value !== '') {
                $input .= $this->formElementAttribute($key, $value);
            }
        }
        return $input . '>' . $errorStr;
    }
}
