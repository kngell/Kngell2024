<?php

declare(strict_types=1);

class LabelElement extends AbstractHtmlElement
{
    protected string|null $content;
    protected string $for;
    protected string $formId;

    public function __construct(string|null $content = null)
    {
        parent::__construct();
        $this->content = $content;
    }

    // public function generate(): string
    // {
    //     $results = [];
    //     $label = '<label';
    //     foreach ($this->children as $child) {
    //         $child->formErrors($this->formErrors);
    //         $child->formValues($this->formValues);
    //         $results[] = $child->generate();
    //     }
    //     foreach ($this as $key => $value) {
    //         if ($key !== 'content' && in_array(gettype($value), ['string', 'bool', 'boolean', 'array', 'integer'])) {
    //             $label .= $this->formElementAttribute($key, $value);
    //         }
    //     }
    //     $label .= '/>';
    //     return  $label . implode(' ', $results) . (! empty($this->content) ? $this->content : '') . (! empty($label) ? '</label>' : '');
    // }

    /**
     * @param array $formErrors
     * @return LabelElement
     */
    #[Override]
    public function formErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    /**
     * @param array $formValues
     * @return LabelElement
     */
    #[Override]
    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    /**
     * @param string $content
     * @return LabelElement
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @param string $for
     * @return LabelElement
     */
    public function for(string $for): self
    {
        $this->for = $for;
        return $this;
    }

    /**
     * @param string $formId
     * @return LabelElement
     */
    public function formId(string $formId): self
    {
        $this->formId = $formId;
        return $this;
    }

    /**
     * @param string $id
     * @return LabelElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array $class
     * @return LabelElement
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }
}