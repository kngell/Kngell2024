<?php

declare(strict_types=1);

class FormBuilder extends AbstractHtmlElement
{
    protected string $name;
    protected string $action;
    protected string $target;
    protected string $method;
    protected string $autocomplete;
    protected string $enctype;
    protected string $rel;
    protected string $acceptCharset;
    protected string $autocapitalize;
    protected bool $novalidate;
    protected string $role;

    public function __construct(TokenInterface $token)
    {
        parent::__construct($token);
        $this->tag = 'form';
    }

    /**
     * @return self
     */
    public function form() : self
    {
        return new self($this->token);
    }

    #[Override]
    public function formErrors(array $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    #[Override]
    public function formValues(array $formValues): self
    {
        $this->formValues = $formValues;
        return $this;
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

    public function accesskey(string $accesskey): self
    {
        $this->accesskey = $accesskey;
        return $this;
    }

    public function contenteditable(string $contenteditable): self
    {
        $this->contenteditable = $contenteditable;
        return $this;
    }

    public function data(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function dir(string $dir): self
    {
        $this->dir = $dir;
        return $this;
    }

    public function draggable(string $draggable): self
    {
        $this->draggable = $draggable;
        return $this;
    }

    public function enterkeyhint(string $enterkeyhint): self
    {
        $this->enterkeyhint = $enterkeyhint;
        return $this;
    }

    public function hidden(string $hidden): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function inert(string $inert): self
    {
        $this->inert = $inert;
        return $this;
    }

    public function inputmode(string $inputmode): self
    {
        $this->inputmode = $inputmode;
        return $this;
    }

    public function lang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function popover(string $popover): self
    {
        $this->popover = $popover;
        return $this;
    }

    public function spellcheck(string $spellcheck): self
    {
        $this->spellcheck = $spellcheck;
        return $this;
    }

    public function style(array $style): self
    {
        $this->style = $style;
        return $this;
    }

    public function tabindex(string $tabindex): self
    {
        $this->tabindex = $tabindex;
        return $this;
    }

    public function translate(string $translate): self
    {
        $this->translate = $translate;
        return $this;
    }

    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function align(string $align): self
    {
        $this->align = $align;
        return $this;
    }

    public function onclick(string $onclick): self
    {
        $this->onclick = $onclick;
        return $this;
    }

    public function ondblclick(string $ondblclick): self
    {
        $this->ondblclick = $ondblclick;
        return $this;
    }

    public function onmousedown(string $onmousedown): self
    {
        $this->onmousedown = $onmousedown;
        return $this;
    }

    public function onmouseup(string $onmouseup): self
    {
        $this->onmouseup = $onmouseup;
        return $this;
    }

    public function onmouseover(string $onmouseover): self
    {
        $this->onmouseover = $onmouseover;
        return $this;
    }

    public function onmousemove(string $onmousemove): self
    {
        $this->onmousemove = $onmousemove;
        return $this;
    }

    public function onmouseout(string $onmouseout): self
    {
        $this->onmouseout = $onmouseout;
        return $this;
    }

    public function onkeypress(string $onkeypress): self
    {
        $this->onkeypress = $onkeypress;
        return $this;
    }

    public function onkeydown(string $onkeydown): self
    {
        $this->onkeydown = $onkeydown;
        return $this;
    }

    public function onkeyup(string $onkeyup): self
    {
        $this->onkeyup = $onkeyup;
        return $this;
    }
}