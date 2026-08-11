<?php

declare(strict_types=1);

final class StepItem
{
    private string $id;
    private string $title;
    private ?string $description = null;
    private array $sectionGroups = [];
    private int $priority = 0;
    private ?string $state = null; // 'active', 'completed', 'disabled', 'default'
    private ?string $icon = null;
    private array $class = [];
    private array $attributes = [];

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

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function addSectionGroup(string $groupKey): self
    {
        $this->sectionGroups[] = $groupKey;
        return $this;
    }

    public function getSectionGroups(): array
    {
        return $this->sectionGroups;
    }

    public function setSectionGroups(array $groups): self
    {
        $this->sectionGroups = $groups;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->state === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->state === 'completed';
    }

    public function isDisabled(): bool
    {
        return $this->state === 'disabled';
    }

    public function isDefault(): bool
    {
        return $this->state === 'default';
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getClass(): array
    {
        return $this->class;
    }

    public function setClass(array $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sectionGroups' => $this->sectionGroups,
            'priority' => $this->priority,
            'state' => $this->state,
            'icon' => $this->icon,
            'class' => $this->class,
            'attributes' => $this->attributes,
        ];
    }

    public static function fromArray(array $data): self
    {
        $step = new self($data['id'], $data['title']);

        if (isset($data['description'])) {
            $step->setDescription($data['description']);
        }

        if (isset($data['sectionGroups'])) {
            $step->setSectionGroups($data['sectionGroups']);
        }

        if (isset($data['priority'])) {
            $step->setPriority($data['priority']);
        }

        if (isset($data['state'])) {
            $step->setState($data['state']);
        }

        if (isset($data['icon'])) {
            $step->setIcon($data['icon']);
        }

        if (isset($data['class'])) {
            $step->setClass($data['class']);
        }

        if (isset($data['attributes'])) {
            $step->setAttributes($data['attributes']);
        }

        return $step;
    }
}