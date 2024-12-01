<?php

declare(strict_types=1);

class LabelElement extends FormElements
{
    protected InputElement $input;
    protected string $content;
    protected string $id;
    protected array $class;
    protected string $for;
    protected string $formId;

    public function __construct(Token $token)
    {
        parent::__construct($token);
    }

    public function makeForm(): string
    {
        $results = [];
        $label = '<label';
        foreach ($this->children as $child) {
            $results[] = $child->makeForm();
        }

        foreach ($this as $key => $value) {
            if ($key !== 'content' && in_array(gettype($value), ['string', 'bool', 'boolean'])) {
                $label .= $this->formElementAttribute($key, $value);
            }
        }
        if (! empty($label)) {
            $label .= '/>' . $this->content;
        }

        return  $label . implode(' ', $results) . (! empty($label) ? '</label>' : '');
    }

    /**
     * Set the value of content.
     *
     * @param string $content
     *
     * @return self
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set the value of For.
     *
     * @param string $for
     *
     * @return self
     */
    public function for(string $for): self
    {
        $this->for = $for;
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
     * Set the value of formId.
     *
     * @param string $formId
     *
     * @return self
     */
    public function formId(string $formId): self
    {
        $this->formId = $formId;
        return $this;
    }
}
