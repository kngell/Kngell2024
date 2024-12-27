<?php

declare(strict_types=1);

class FormBuilder extends AbstractFormElement
{
    private TokenInterface $token;
    private string $name;
    private string $action;
    private string $target;
    private string $method;
    private string $autocomplete;
    private bool $enctype;
    private string $rel;
    private string $acceptCharset;
    private string $autocapitalize;
    private bool $novalidate;
    private string $role;

    public function __construct(TokenInterface $token)
    {
        parent::__construct();
        $this->token = $token;
    }

    public function makeForm(): string
    {
        $results = [];
        /** @var AbstractForm $child */
        foreach ($this->children as $child) {
            $child->formErrors($this->formErrors);
            $child->formValues($this->formValues);
            $results[] = $child->makeForm();
        }
        return $this->begin() . $this->frmName() . implode(' ', $results) . $this->end();
    }

    /**
     * @return self
     */
    public function form() : self
    {
        return new self($this->token);
    }

    /**
     * @param string $type
     * @return AbstractInput
     */
    public function input(string $type) : AbstractInput
    {
        return match (strtolower($type)) {
            'text' => new TextType(),
            'radio' => new RadioType(),
            'hidden' => new HiddenType(),
            'email' => new EmailType(),
            'password' => new PasswordType(),
            'checkbox' => new CheckBoxType(),
            'submit' => new SubmitType()
        };
    }

    /**
     * @param string|null $message
     * @return LabelElement
     */
    public function label(string|null $message = null) : LabelElement
    {
        return new LabelElement($message);
    }

    /**
     * @return TextAreaElement
     */
    public function textArea() : TextAreaElement
    {
        return new TextAreaElement();
    }

    /**
     * @return SelectElement
     */
    public function select() : SelectElement
    {
        return new SelectElement($this->token);
    }

    /**
     * @return ButtonElement
     */
    public function button() : ButtonElement
    {
        return new ButtonElement();
    }

    /**
     * @param string $tag
     * @return CustomHtmlElement
     */
    public function htmlTag(string $tag) : CustomHtmlElement
    {
        return new CustomHtmlElement($tag);
    }

    /**
     * @param string $tag
     * @return FormElementWrapper
     */
    public function wrapper(string $tag) : FormElementWrapper
    {
        return new FormElementWrapper($tag);
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
     * @param bool $novalidate
     * @return FormBuilder
     */
    public function novalidate(bool $novalidate): self
    {
        $this->novalidate = $novalidate;
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
     * @param array $formErrors
     * @return FormBuilder
     */
    public function formErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    /**
     * @param array $formValues
     * @return FormBuilder
     */
    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
    }

    /**
     * @param string $role
     * @return FormBuilder
     */
    public function role(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @param array $style
     * @return FormBuilder
     */
    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    private function begin() : string
    {
        $formBegin = '<form';
        foreach ($this as $prop => $value) {
            if ($prop !== 'formErrors' && $prop !== 'formValues') {
                $formBegin .= match (true) {
                    in_array(gettype($value), ['string', 'bool', 'boolean']) => ' ' . $this->property($prop, $value),
                    is_array($value) => ' ' . $this->property($prop, implode(' ', $value)),
                    default => ''
                };
            }
        }
        return $formBegin . '>' . $this->csrftoken();
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

    private function end() : string
    {
        return '</form>';
    }

    private function csrftoken() : string
    {
        return (new HiddenType)->name('csrfToken')->value($this->token->create(8, $this->name ?? ''))->makeForm();
    }

    private function frmName() : string
    {
        return (new HiddenType)->name('frm_name')->value($this->name ?? '')->makeForm();
    }
}