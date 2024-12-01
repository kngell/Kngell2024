<?php

declare(strict_types=1);

class SelectElement extends FormElements
{
    protected string $name;
    protected string $id;
    protected array $class;
    /** @var SelectOption[] */
    protected array $options;

    public function __construct(Token $token)
    {
        parent::__construct($token);
    }

    public function makeForm(): string
    {
        $select = '<select';
        foreach ($this as $key => $value) {
            if (in_array(gettype($value), ['string', 'bool', 'boolean'])) {
                $select .= $this->formElementAttribute($key, $value);
            }
        }
        $select .= '>';
        $options = $this->getOptions();
        $results = [];
        foreach ($this->children as $child) {
            $results[] = $child->makeForm();
        }
        return  $select . implode(' ', array_merge($options, $results)) . '</select>';
    }

    public function addOptions(mixed $value, string $content) : self
    {
        $this->add(new SelectOption($value, $content));
        return $this;
    }

    /**
     * Set the value of name.
     *
     * @param string $name
     *
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of id.
     *
     * @param string $id
     *
     * @return self
     */
    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the value of class.
     *
     * @param array $class
     *
     * @return self
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param SelectOption ...$options
     * @return SelectElement
     */
    public function options(SelectOption ...$options) : self
    {
        $this->options = $options;
        return $this;
    }

    private function getOptions() : array
    {
        $options = [];
        if (isset($this->options)) {
            foreach ($this->options as $option) {
                $options[] = $option->makeForm();
            }
        }
        return $options;
    }
}
