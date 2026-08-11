<?php

declare(strict_types=1);

final class TabItem
{
    private string $id;
    private string $title;
    private array $sectionGroups = [];
    private ?string $state = null; // 'active', 'disabled', null
    private array $contentClass = [];
    private array $attributes = [];
    private ?string $icon = null;
    private int $priority = 0;

    public function __construct(string $id, string $title)
    {
        $this->id = $id;
        $this->title = $title;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSectionGroups(): array
    {
        return $this->sectionGroups;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setSectionGroups(array $groups): self
    {
        $this->sectionGroups = $groups;
        return $this;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->state === 'disabled';
    }

    public function isActive(): bool
    {
        return $this->state === 'active';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sectionGroups' => $this->sectionGroups,
            'state' => $this->state,
            'contentClass' => $this->contentClass,
            'icon' => $this->icon,
            'priority' => $this->priority,
        ];
    }

    /**
     * @return array
     */
    public function getContentClass(): array
    {
        return $this->contentClass;
    }

    /**
     * @param array $contentClass
     *
     * @return TabItem
     */
    public function setContentClass(array $contentClass): TabItem
    {
        $this->contentClass = $contentClass;

        return $this;
    }

    public static function create(string $id, string $title): self
    {
        return new self($id, $title);
    }
}