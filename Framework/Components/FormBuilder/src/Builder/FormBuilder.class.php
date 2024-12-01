<?php

declare(strict_types=1);

class FormBuilder
{
    private Token $token;
    private string $name;
    private string $action;
    private string $target;
    private string $method;
    private string $autocomplete;
    private bool $novalidate;
    private bool $enctype;
    private string $rel;
    private string $acceptCharset;
    private string $autocapitalize;
    private string $id;
    private array $class;

    public function __construct(Token $token)
    {
        $this->token = $token;
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

    public function add(FormElement ...$formElements) : FormElement
    {
        $form = $this->build()->add($this->csrftoken());
        /** @var FormElement $formElement */
        foreach ($formElements as $formElement) {
            $form->add($formElement);
        }
        return $form;
    }

    public function input(string $type) : InputElement
    {
        return match ($type) {
            'text' => new TextType(),
            'radio' => new RadioType(),
        };
    }

    public function label() : LabelElement
    {
        return new LabelElement($this->token);
    }

    public function select() : SelectElement
    {
        return new SelectElement($this->token);
    }

    private function csrftoken() : InputElement
    {
        return (new HiddenType)->name('csrfToken')->value($this->token->create(8, $this->name ?? ''));
    }

    private function build() : Form
    {
        $myForm = new Form($this->token);
        $form = new ReflectionClass(Form::class);
        foreach ($form->getProperties() as $prop) {
            if (isset($this->{$prop->getName()})) {
                $prop->setAccessible(true);
                $value = $this->{$prop->getName()};
                $prop->setValue($myForm, $value);
            }
        }
        return $myForm;
    }
}
