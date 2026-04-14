<?php

declare(strict_types=1);

class RegularSectionConfig
{
    private string $title;
    private string $key;
    private string $sectionClass = 'form-section';
    private string $wrapperClass = '';
    private string $icon = 'icon-edit';
    private bool $showRequired = false;
    private array $customAttributes = [];

    public function __construct(string $title, string $key)
    {
        $this->title = $title;
        $this->key = $key;
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

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function getSectionClass(): string
    {
        return $this->sectionClass;
    }

    public function setSectionClass(string $class): self
    {
        $this->sectionClass = $class;
        return $this;
    }

    public function getWrapperClass(): string
    {
        return $this->wrapperClass;
    }

    public function setWrapperClass(string $class): self
    {
        $this->wrapperClass = $class;
        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function showRequired(): bool
    {
        return $this->showRequired;
    }

    public function setShowRequired(bool $show): self
    {
        $this->showRequired = $show;
        return $this;
    }

    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    public function setCustomAttributes(array $attrs): self
    {
        $this->customAttributes = $attrs;
        return $this;
    }

    public static function create(string $title, string $key): self
    {
        return new self($title, $key);
    }
}