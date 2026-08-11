<?php

declare(strict_types=1);

abstract class BaseFooterDTO implements ModalDTOInterface
{
    public function __construct(
        protected string $cancelRoute,
        protected string $deleteRoute,
        protected bool $isVisible = false,
        protected null|string|int $id = null,
        protected int $sortOrder = 0,
        protected bool $isActive = true,
        protected ?string $validFrom = null,
        protected ?string $validTo = null,
    ) {
    }

    // ─── Getters ──────────────────────────────────────────────

    public function getCancelRoute(): string
    {
        return $this->cancelRoute;
    }

    public function getDeleteRoute(): string
    {
        return $this->deleteRoute;
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function getId(): null|string|int
    {
        return $this->id;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getValidFrom(): ?string
    {
        return $this->validFrom;
    }

    public function getValidTo(): ?string
    {
        return $this->validTo;
    }

    // ─── Setters with static return type ──────────────────────

    public function setVisible(bool $visible): static
    {
        $this->isVisible = $visible;
        return $this;
    }

    public function setId(null|string|int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function setActive(bool $active): static
    {
        $this->isActive = $active;
        return $this;
    }

    public function setValidFrom(?string $validFrom): static
    {
        $this->validFrom = $validFrom;
        return $this;
    }

    public function setValidTo(?string $validTo): static
    {
        $this->validTo = $validTo;
        return $this;
    }

    public function toFormValues(): array
    {
        return [
            'id' => $this->id,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
            'valid_from' => $this->validFrom,
            'valid_to' => $this->validTo,
        ];
    }
}