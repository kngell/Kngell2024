<?php

declare(strict_types=1);

class FormBuilder extends AbstractHtmlElement
{
    private string $name;
    private string $action;
    private string $target;
    private string $method;
    private string $autocomplete;
    private string $enctype;
    private string $rel;
    private string $acceptCharset;
    private string $autocapitalize;
    private bool $novalidate;
    private string $role;

    public function __construct(TokenInterface $token)
    {
        parent::__construct($token);
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
    public function button(string $type = '') : ButtonElement
    {
        return new ButtonElement($type);
    }

    public function tag(string $tag) : HtmlBuilder|HtmlaElement|HtmlTagElement
    {
        return (new HtmlBuilder($this->token))->tag($tag);
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
        $this->method = strtoupper($method);
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
     * @param string $enctype
     * @return FormBuilder
     */
    public function enctype(string $enctype): self
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
     * @param string $name
     * @return FormBuilder
     */
    public function name(string $name): self
    {
        $this->name = $name;
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
}