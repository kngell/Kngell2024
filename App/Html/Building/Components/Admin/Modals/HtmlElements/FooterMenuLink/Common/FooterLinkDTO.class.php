<?php

declare(strict_types=1);

final class FooterLinkDTO extends BaseFooterDTO
{
    private null|int|string $columnId;
    private ?TargetAttr $target;
    private ?string $url;
    private ?string $title;

    public function __construct(
        string $cancelRoute,
        string $deleteRoute,
        null|int|string $columnId = null,
        ?TargetAttr $target = TargetAttr::SELF,
        bool $isVisible = false,
        null|string|int $id = null,
        ?string $url = null,
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

        $this->columnId = $columnId;
        $this->target = $target;
        $this->url = $url;
        $this->title = $title;
    }

    // ─── Getters ──────────────────────────────────────────────

    public function getColumnId(): null|int|string
    {
        return $this->columnId;
    }

    public function getTarget(): ?TargetAttr
    {
        return $this->target;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    // ─── Setters with static return type ──────────────────────

    public function setColumnId(null|int|string $columnId): static
    {
        $this->columnId = $columnId;
        return $this;
    }

    public function setTarget(?TargetAttr $target): static
    {
        $this->target = $target;
        return $this;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
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
            'column_id' => $this->columnId,
            'title' => $this->title,
            'url' => $this->url,
            'target' => $this->target !== null ? $this->target->value : null,
        ]);
    }
}