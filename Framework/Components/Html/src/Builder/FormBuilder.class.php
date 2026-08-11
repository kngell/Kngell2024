<?php

declare(strict_types=1);

class FormBuilder extends HtmlBuilder
{
    // ── Form-Specific Properties ────────────────────────────────────
    protected string $action = '';
    protected string $target = '';
    protected string $method = 'POST';
    protected string $autocomplete = '';
    protected string $enctype = '';
    protected string $rel = '';
    protected string $acceptCharset = '';
    protected string $autocapitalize = '';
    protected bool $novalidate = false;

    public function __construct(
        TokenInterface $token,
        ?TranslatorServiceInterface $translator = null,
        bool $includeCsrftoken = true,
    ) {
        parent::__construct($token, $translator, 'form');
        $this->includeToken = $includeCsrftoken;
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