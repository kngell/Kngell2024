<?php

declare(strict_types=1);

class ButtonElement extends AbstractHtmlElement implements HtmlAttributeProviderInterface
{
    // ── Button-Specific Properties ──────────────────────────────────
    private string $type = '';
    private bool $autofocus = false;
    private string $command = '';
    private string $commandfor = '';
    private string $form = '';
    private string $formaction = '';
    private string $formenctype = '';
    private string $formmethod = '';
    private bool $formnovalidate = false;
    private string $formtarget = '';
    private string $popovertarget = '';
    private string $popovertargetaction = '';

    public function __construct(string $type = '')
    {
        $this->type = $type;
        $this->tag = 'button';
        parent::__construct();
    }

    // ── HtmlAttributeProviderInterface ──────────────────────────────

    public function getProperties(): array
    {
        return get_object_vars($this);
    }

    // ── Button-Specific Setters ─────────────────────────────────────

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function autofocus(bool $autofocus = true): static
    {
        $this->autofocus = $autofocus;
        return $this;
    }

    public function command(string $command): static
    {
        $this->command = $command;
        return $this;
    }

    public function commandfor(string $commandfor): static
    {
        $this->commandfor = $commandfor;
        return $this;
    }

    public function form(string $form): static
    {
        $this->form = $form;
        return $this;
    }

    public function formaction(string $formaction): static
    {
        $this->formaction = $formaction;
        return $this;
    }

    public function formenctype(string $formenctype): static
    {
        $this->formenctype = $formenctype;
        return $this;
    }

    public function formmethod(string $formmethod): static
    {
        $this->formmethod = $formmethod;
        return $this;
    }

    public function formnovalidate(bool $formnovalidate = true): static
    {
        $this->formnovalidate = $formnovalidate;
        return $this;
    }

    public function formtarget(string $formtarget): static
    {
        $this->formtarget = $formtarget;
        return $this;
    }

    public function popovertarget(string $popovertarget): static
    {
        $this->popovertarget = $popovertarget;
        return $this;
    }

    public function popovertargetaction(string $popovertargetaction): static
    {
        $this->popovertargetaction = $popovertargetaction;
        return $this;
    }
}