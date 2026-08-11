<?php

declare(strict_types=1);

final class FooterColumnDTO extends BaseFooterDTO
{
    private ?string $columnKey;
    private ?string $title;

    public function __construct(
        string $cancelRoute,
        string $deleteRoute,
        ?int $id = null,
        bool $isVisible = false,
        ?string $columnKey = null,
        ?string $title = null,
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

        $this->columnKey = $columnKey;
        $this->title = $title;
    }

    // ─── Getters ──────────────────────────────────────────────

    public function getColumnKey(): ?string
    {
        return $this->columnKey;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    // ─── Setters with static return type ──────────────────────

    public function setColumnKey(?string $columnKey): static
    {
        $this->columnKey = $columnKey;
        return $this;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function toFormValues(): array
    {
        return array_merge(parent::toFormValues(), [
            'column_key' => $this->columnKey,
            'title' => $this->title,
        ]);
    }
}