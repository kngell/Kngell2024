<?php

declare(strict_types=1);

final class FooterSocialDTO extends BaseFooterDTO
{
    private ?string $platform;
    private ?string $name;
    private ?string $url;
    private ?string $icon;
    private ?string $iconClass;

    public function __construct(
        string $cancelRoute,
        string $deleteRoute,
        null|int|string $id = null,
        bool $isVisible = false,
        ?string $platform = null,
        ?string $name = null,
        ?string $url = null,
        ?string $icon = null,
        ?string $iconClass = null,
        int $sortOrder = 0,
        bool $isActive = true,
        ?string $validFrom = null,
        ?string $validTo = null,
    ) {
        parent::__construct(
            cancelRoute: $cancelRoute,
            deleteRoute: $deleteRoute,
            isVisible: $isVisible,
            id: $id,
            sortOrder: $sortOrder,
            isActive: $isActive,
            validFrom: $validFrom,
            validTo: $validTo,
        );

        $this->platform = $platform;
        $this->name = $name;
        $this->url = $url;
        $this->icon = $icon;
        $this->iconClass = $iconClass;
    }

    // ─── Getters ──────────────────────────────────────────────

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getIconClass(): ?string
    {
        return $this->iconClass;
    }

    // ─── Setters with static return type ──────────────────────

    public function setPlatform(?string $platform): static
    {
        $this->platform = $platform;
        return $this;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function setIconClass(?string $iconClass): static
    {
        $this->iconClass = $iconClass;
        return $this;
    }

    public function toFormValues(): array
    {
        return array_merge(parent::toFormValues(), [
            'platform' => $this->platform,
            'name' => $this->name,
            'url' => $this->url,
            'icon' => $this->icon,
            'icon_class' => $this->iconClass,
        ]);
    }
}