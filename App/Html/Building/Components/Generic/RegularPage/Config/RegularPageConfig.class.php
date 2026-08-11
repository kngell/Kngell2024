<?php

declare(strict_types=1);

class RegularPageConfig implements RegularPageConfigInterface
{
    public function __construct(
        private string $enumClass,
        private array $assets = [],
        private ?string $expectedControllerClass = null,
        private array $sections = [],
    ) {
    }

    public function getEnumClass(): string
    {
        return $this->enumClass;
    }

    public function setEnumClass(string $enumClass): self
    {
        $this->enumClass = $enumClass;
        return $this;
    }

    public function getAssets(): array
    {
        return $this->assets;
    }

    public function setAssets(array $assets): self
    {
        $this->assets = $assets;
        return $this;
    }

    public function getExpectedControllerClass(): ?string
    {
        return $this->expectedControllerClass;
    }

    public function setExpectedControllerClass(?string $expectedControllerClass): self
    {
        $this->expectedControllerClass = $expectedControllerClass;
        return $this;
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function setSections(array $sections): self
    {
        $this->sections = $sections;
        return $this;
    }

    public static function create(string $enumClass): self
    {
        return new self($enumClass);
    }
}