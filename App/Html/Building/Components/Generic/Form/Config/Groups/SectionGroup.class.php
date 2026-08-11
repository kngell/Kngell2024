<?php

declare(strict_types=1);

final class SectionGroup
{
    private string $key;
    private array $sectionKeys = [];
    private string $position = 'left'; // left, right, full
    private array $wrapperClass = [];
    private array $attributes = [];
    private string $wrapperTag = 'div';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getSectionKeys(): array
    {
        return $this->sectionKeys;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getWrapperClass(): array
    {
        return $this->wrapperClass;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setSectionKeys(array $keys): self
    {
        $this->sectionKeys = $keys;
        return $this;
    }

    public function addSection(string $sectionKey): self
    {
        $this->sectionKeys[] = $sectionKey;
        return $this;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function setWrapperClass(array $class): self
    {
        $this->wrapperClass = $class;
        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function isLeft(): bool
    {
        return $this->position === 'left';
    }

    public function isRight(): bool
    {
        return $this->position === 'right';
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'sections' => $this->sectionKeys,
            'position' => $this->position,
            'wrapperClass' => $this->wrapperClass,
            'wrapperTag' => $this->wrapperTag,
        ];
    }

    /**
     * @return string
     */
    public function getWrapperTag(): string
    {
        return $this->wrapperTag;
    }

    /**
     * @param string $wrapperTag
     *
     * @return SectionGroup
     */
    public function setWrapperTag(string $wrapperTag): SectionGroup
    {
        $this->wrapperTag = $wrapperTag;

        return $this;
    }

    public static function create(string $key): self
    {
        return new self($key);
    }
}