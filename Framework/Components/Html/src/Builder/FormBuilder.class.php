<?php

declare(strict_types=1);

class FormBuilder extends AbstractHtmlElement
{
    use ElementFactoryTrait;

    // ── Form-Specific Properties ────────────────────────────────────
    protected string $action = '';
    protected string $target = '';
    protected string $method = '';
    protected string $autocomplete = '';
    protected string $enctype = '';
    protected string $rel = '';
    protected string $acceptCharset = '';
    protected string $autocapitalize = '';
    protected bool $novalidate = false;

    public function __construct(TokenInterface $token, bool $includeCsrftoken = true)
    {
        parent::__construct($token);
        $this->tag = 'form';
        $this->includeToken = $includeCsrftoken;
    }

    public function form(): static
    {
        return new self($this->token, $this->includeToken);
    }

    public function textArea(): TextAreaElement
    {
        return new TextAreaElement();
    }

    public function tag(string $tag): HtmlBuilder|HtmlaElement|HtmlTagElement|self
    {
        return (new HtmlBuilder($this->token))->tag($tag);
    }

    // ── Form-Specific Setters ───────────────────────────────────────

    public function action(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function target(string $target): static
    {
        $this->target = $target;
        return $this;
    }

    public function method(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    public function autocomplete(string $autocomplete): static
    {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    public function enctype(string $enctype): static
    {
        $this->enctype = $enctype;
        return $this;
    }

    public function rel(string $rel): static
    {
        $this->rel = $rel;
        return $this;
    }

    public function acceptCharset(string $acceptCharset): static
    {
        $this->acceptCharset = $acceptCharset;
        return $this;
    }

    public function autocapitalize(string $autocapitalize): static
    {
        $this->autocapitalize = $autocapitalize;
        return $this;
    }

    public function novalidate(bool $novalidate = true): static
    {
        $this->novalidate = $novalidate;
        return $this;
    }
}