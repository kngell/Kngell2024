<?php

declare(strict_types=1);

class ButtonElement extends AbstractHtmlElement
{
    private string $name;
    private string $value;
    private string $type;
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

    public function __construct(string $type = '')
    {
        $this->type = $type;
        $this->tag = 'button';
        parent::__construct();
    }

    public function custom(array $custom): self
    {
        $this->custom = $custom;
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
     * @param mixed $value
     * @return ButtonElement
     */
    public function value(mixed $value): self
    {
        $this->value = $value;
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
     * @param string ...$class
     * @return ButtonElement
     */
    public function class(string ...$class): self
    {
        $this->class = $class;
        return $this;
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
     * @param string $content
     * @param bool $contentUp
     * @return ButtonElement
     */
    public function content(string $content, bool $contentUp = true): self
    {
        $this->content = $content;
        $this->contentUp = $contentUp;
        return $this;
    }

    /**
     * @param int $tabindex
     * @return ButtonElement
     */
    public function tabindex(int $tabindex): self
    {
        $this->tabindex = $tabindex;
        return $this;
    }
}