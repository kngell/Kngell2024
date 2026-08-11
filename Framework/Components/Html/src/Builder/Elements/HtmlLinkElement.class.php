<?php

declare(strict_types=1);

class HtmlLinkElement extends AbstractHtmlElement
{
    private const string TAG = 'a';

    // ── Anchor-Specific Properties ──────────────────────────────────
    private string $attributionsrc = '';
    private string $download = '';
    private string $hreflang = '';
    private string $ping = '';
    private string $referrerpolicy = '';
    private string $rel = '';
    private string $target = '';
    private string $type = '';

    public function __construct()
    {
        parent::__construct();
        $this->tag = self::TAG;
    }

    // ── Anchor-Specific Setters ─────────────────────────────────────
    public function attributionsrc(string $attributionsrc): static
    {
        $this->attributionsrc = $attributionsrc;
        return $this;
    }

    public function download(string $download): static
    {
        $this->download = $download;
        return $this;
    }

    public function hreflang(string $hreflang): static
    {
        $this->hreflang = $hreflang;
        return $this;
    }

    public function ping(string $ping): static
    {
        $this->ping = $ping;
        return $this;
    }

    public function referrerpolicy(string $referrerpolicy): static
    {
        $this->referrerpolicy = $referrerpolicy;
        return $this;
    }

    public function rel(string $rel): static
    {
        $this->rel = $rel;
        return $this;
    }

    public function target(string $target): static
    {
        $this->target = $target;
        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }
}