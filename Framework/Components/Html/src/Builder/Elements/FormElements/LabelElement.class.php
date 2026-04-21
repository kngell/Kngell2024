<?php

declare(strict_types=1);

class LabelElement extends AbstractHtmlElement
{
    // ── Label-Specific Properties ───────────────────────────────────
    protected string $for = '';
    protected string $formId = '';

    public function __construct(?string $content = null)
    {
        parent::__construct();
        $this->content = $content;
        $this->tag = 'label';
    }

    // ── Label-Specific Setters ──────────────────────────────────────

    public function for(string $for): static
    {
        $this->for = $for;
        return $this;
    }

    public function formId(string $formId): static
    {
        $this->formId = $formId;
        return $this;
    }
}