<?php

declare(strict_types=1);
class FormElementWrapper extends AbstractFormElement
{
    private string $tag;

    public function __construct(string $wrapperTag)
    {
        parent::__construct();
        $this->tag = $wrapperTag;
    }

    public function makeForm(): string
    {
        $results = [];
        $wrapper = '<' . $this->tag;
        foreach ($this->children as $child) {
            $child->formErrors($this->formErrors);
            $child->formValues($this->formValues);
            $results[] = $child->makeForm();
        }
        foreach ($this as $key => $value) {
            if ($key !== 'tag' && in_array(gettype($value), ['string', 'bool', 'boolean', 'array'])) {
                $wrapper .= $this->formElementAttribute($key, $value);
            }
        }
        $wrapper .= '/>';
        return  $wrapper . implode(' ', $results) . '</' . $this->tag . '>';
    }

    /**
     * @param string $id
     * @return FormElementWrapper
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array $class
     * @return FormElementWrapper
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param array $style
     * @return self
     */
    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }
}