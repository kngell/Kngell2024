<?php

declare(strict_types=1);

class FormBuilder extends FormElements
{
    private string $name;
    private string $action;
    private string $target;
    private string $method;
    private string $autocomplete;
    private bool $enctype;
    private string $rel;
    private string $acceptCharset;
    private string $autocapitalize;
    private string $id;
    private array $class;
    private bool $novalidate;

    public function __construct(Token $token)
    {
        parent::__construct($token);
    }

    public function makeForm(): string
    {
        $results = [];
        /** @var FormElement $child */
        foreach ($this->children as $child) {
            $child->setFormErrors($this->formErrors);
            $child->setFormValues($this->formValues);
            $results[] = $child->makeForm();
        }
        return $this->begin() . implode(' ', $results) . $this->end();
    }

    public function begin() : string
    {
        $formBegin = '<form';
        foreach ($this as $prop => $value) {
            $formBegin .= match (true) {
                in_array(gettype($value), ['string', 'bool', 'boolean']) => ' ' . $this->property($prop, $value),
                is_array($value) => ' ' . $this->property($prop, implode(' ', $value)),
                default => ''
            };
        }
        // $formBegin .= ' csrfToken=' . $this->token->create(8, $this->name ?? '');
        return $formBegin . '>' . $this->csrftoken()->makeForm();
    }

    public function end() : string
    {
        return '</form>';
    }

    /**
     * @return FormBuilder
     */
    public function form() : self
    {
        return new self($this->token);
    }

    public function input(string $type) : InputElement
    {
        return match ($type) {
            'text' => new TextType(),
            'radio' => new RadioType(),
            'hidden' => new HiddenType()
        };
    }

    public function label() : LabelElement
    {
        return new LabelElement($this->token);
    }

    public function textArea() : TextAreaElement
    {
        return new TextAreaElement();
    }

    public function select() : SelectElement
    {
        return new SelectElement($this->token);
    }

    public function button() : ButtonElement
    {
        return new ButtonElement();
    }

    public function htmlTag(string $tag) : CustomHtmlElement
    {
        return new CustomHtmlElement($tag);
    }

    /**
     * @param string $name
     * @return FormBuilder
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $action
     * @return FormBuilder
     */
    public function action(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param string $target
     * @return FormBuilder
     */
    public function target(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @param string $method
     * @return FormBuilder
     */
    public function method(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param string $autocomplete
     * @return FormBuilder
     */
    public function autocomplete(string $autocomplete): self
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    /**
     * @param bool $novalidate
     * @return FormBuilder
     */
    public function novalidate(bool $novalidate): self
    {
        $this->novalidate = $novalidate;
        return $this;
    }

    /**
     * @param bool $enctype
     * @return FormBuilder
     */
    public function enctype(bool $enctype): self
    {
        $this->enctype = $enctype;
        return $this;
    }

    /**
     * @param string $rel
     * @return FormBuilder
     */
    public function rel(string $rel): self
    {
        $this->rel = $rel;
        return $this;
    }

    /**
     * @param string $acceptCharset
     * @return FormBuilder
     */
    public function acceptCharset(string $acceptCharset): self
    {
        $this->acceptCharset = $acceptCharset;
        return $this;
    }

    /**
     * @param string $autocapitalize
     * @return FormBuilder
     */
    public function autocapitalize(string $autocapitalize): self
    {
        $this->autocapitalize = $autocapitalize;
        return $this;
    }

    /**
     * @param string $id
     * @return FormBuilder
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array $class
     * @return FormBuilder
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    private function csrftoken() : HiddenType
    {
        return (new HiddenType)->name('csrfToken')->value($this->token->create(8, $this->name ?? ''));
    }

    private function property(string $prop, string|bool $value) : string
    {
        return match ($prop) {
            'action' => $prop . '="/' . $value . '"',
            'novalidate' => 'novalidate',
            'acceptCharset' => 'accept-charset="' . $value . '"',
            'enctype' => $prop . "='multipart/form-data'",
            'class' => 'class="' . $value . '"',
            default => $prop . '="' . $value . '"',
        };
    }
}