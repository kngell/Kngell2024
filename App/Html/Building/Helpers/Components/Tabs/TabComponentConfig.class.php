<?php

declare(strict_types=1);

final class TabComponentConfig
{
    // ─── Container (outer wrapper) ───
    private array $containerClass = ['tabs'];
    private string $containerTag = 'div';

    // ─── Content Container (wraps tab panels) ───
    private array $contentContainerClass = ['tabs__content'];
    private string $contentContainerTag = 'div';

    // ─── Tab Panel (individual tab content) ───
    private array $panelClass = ['tab-content'];

    // ─── Behavior ───
    private bool $returnAsArray = false;
    private string $radioName = 'tab-name';
    private bool $wrapContent = true;

    // ─── Container Classes ───

    public function getContainerClass(): array
    {
        return $this->containerClass;
    }

    /**
     * Merge classes with defaults.
     */
    public function setContainerClass(array $class): self
    {
        $this->containerClass = $this->mergeClasses($this->containerClass, $class);
        return $this;
    }

    public function getContainerTag(): string
    {
        return $this->containerTag;
    }

    public function setContainerTag(string $tag): self
    {
        $this->containerTag = $tag;
        return $this;
    }

    // ─── Content Container Classes ───

    public function getContentContainerClass(): array
    {
        return $this->contentContainerClass;
    }

    /**
     * Merge classes with defaults.
     */
    public function setContentContainerClass(array $class): self
    {
        $this->contentContainerClass = $this->mergeClasses($this->contentContainerClass, $class);
        return $this;
    }

    public function getContentContainerTag(): string
    {
        return $this->contentContainerTag;
    }

    public function setContentContainerTag(string $tag): self
    {
        $this->contentContainerTag = $tag;
        return $this;
    }

    // ─── Panel Classes ───

    public function getPanelClass(): array
    {
        return $this->panelClass;
    }

    /**
     * Merge classes with defaults.
     */
    public function setPanelClass(array $class): self
    {
        $this->panelClass = $this->mergeClasses($this->panelClass, $class);
        return $this;
    }

    // ─── Behavior ───

    public function isReturnAsArray(): bool
    {
        return $this->returnAsArray;
    }

    public function setReturnAsArray(bool $returnAsArray): self
    {
        $this->returnAsArray = $returnAsArray;
        return $this;
    }

    public function getRadioName(): string
    {
        return $this->radioName;
    }

    public function setRadioName(string $name): self
    {
        $this->radioName = $name;
        return $this;
    }

    public function shouldWrapContent(): bool
    {
        return $this->wrapContent;
    }

    public function setWrapContent(bool $wrap): self
    {
        $this->wrapContent = $wrap;
        return $this;
    }

    // ─── Helper ───

    private function mergeClasses(array $default, array $additional): array
    {
        return array_values(array_unique(array_filter(array_merge($default, $additional))));
    }

    // ─── Presets ───

    public static function create(): self
    {
        return new self();
    }

    public static function simpleTabs(): self
    {
        return self::create()
            ->setContainerClass(['tabs'])
            ->setContainerTag('div')
            ->setContentContainerClass(['tabs__content'])
            ->setPanelClass(['tab-content'])
            ->setWrapContent(true)
            ->setReturnAsArray(false)
            ->setRadioName('tab-name');
    }

    public static function adminForm(): self
    {
        return self::create()
            ->setContainerClass(['tabs'])
            ->setPanelClass(['tab-content'])
            ->setWrapContent(true)
            ->setReturnAsArray(true)
            ->setRadioName('form-tab');
    }

    public static function productTabs(): self
    {
        return self::create()
            ->setContainerClass(['container', 'products'])
            ->setContainerTag('section')
            ->setContentContainerClass(['tab-content-container'])
            ->setContentContainerTag('div')
            ->setPanelClass(['tab-content'])
            ->setWrapContent(true)
            ->setReturnAsArray(false)
            ->setRadioName('product-tab');
    }
}