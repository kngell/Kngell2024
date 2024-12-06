<?php

declare(strict_types=1);

class ButtonElement extends FormElement
{
    private string $type;
    private string $value;
    private string $name;
    private string $id;
    private array $class;
    private bool $autofocus;
    private string $command;
    private string $commandfor;
    private bool $disabled;
    private string $form;
    private string $formaction;
    private string $formenctype;
    private string $formmethod;
    private bool $formnovalidate;
    private string $formtarget;
    private string $popovertarget;
    private string $popovertargetaction;
    private string $content;

    public function makeForm(): string
    {
        $button = '<button';
        foreach ($this as $key => $value) {
            if ($key !== 'content' && in_array(gettype($value), ['string', 'bool', 'boolean', 'array'])) {
                $button .= $this->formElementAttribute($key, $value);
            }
        }
        $button .= '>' . ($this->content ?? '');
        return $button . '</button>';
    }

    /**
     * @param string $type
     * @return ButtonElement
     */
    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $value
     * @return ButtonElement
     */
    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return ButtonElement
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $id
     * @return ButtonElement
     */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array $class
     * @return ButtonElement
     */
    public function class(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param bool $autofocus
     * @return ButtonElement
     */
    public function autofocus(bool $autofocus): self
    {
        $this->autofocus = $autofocus;
        return $this;
    }

    /**
     * @param string $command
     * @return ButtonElement
     */
    public function command(string $command): self
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @param string $commandfor
     * @return ButtonElement
     */
    public function commandfor(string $commandfor): self
    {
        $this->commandfor = $commandfor;
        return $this;
    }

    /**
     * @param bool $disabled
     * @return ButtonElement
     */
    public function disabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * @param string $form
     * @return ButtonElement
     */
    public function form(string $form): self
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param string $formaction
     * @return ButtonElement
     */
    public function formaction(string $formaction): self
    {
        $this->formaction = $formaction;
        return $this;
    }

    /**
     * @param string $formenctype
     * @return ButtonElement
     */
    public function formenctype(string $formenctype): self
    {
        $this->formenctype = $formenctype;
        return $this;
    }

    /**
     * @param string $formmethod
     * @return ButtonElement
     */
    public function formmethod(string $formmethod): self
    {
        $this->formmethod = $formmethod;
        return $this;
    }

    /**
     * @param bool $formnovalidate
     * @return ButtonElement
     */
    public function formnovalidate(bool $formnovalidate): self
    {
        $this->formnovalidate = $formnovalidate;
        return $this;
    }

    /**
     * @param string $formtarget
     * @return ButtonElement
     */
    public function formtarget(string $formtarget): self
    {
        $this->formtarget = $formtarget;
        return $this;
    }

    /**
     * @param string $popovertarget
     * @return ButtonElement
     */
    public function popovertarget(string $popovertarget): self
    {
        $this->popovertarget = $popovertarget;
        return $this;
    }

    /**
     * @param string $popovertargetaction
     * @return ButtonElement
     */
    public function popovertargetaction(string $popovertargetaction): self
    {
        $this->popovertargetaction = $popovertargetaction;
        return $this;
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
}