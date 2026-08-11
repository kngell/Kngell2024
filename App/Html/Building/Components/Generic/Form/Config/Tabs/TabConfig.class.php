<?php

declare(strict_types=1);

final class TabConfig
{
    /** @var TabItem[] */
    private array $tabs = [];

    private array $tabLabelContainerClass = ['tabs__label'];
    private array $contentContainerClass = ['tabs__content'];
    private ?string $defaultTabId = null;
    private ?string $activeTabId = null;

    // ─── Tab Management ───

    public function addTab(TabItem $tab): self
    {
        $this->tabs[$tab->getId()] = $tab;

        if ($tab->isActive() && $this->defaultTabId === null) {
            $this->defaultTabId = $tab->getId();
        }

        return $this;
    }

    public function removeTab(string $tabId): self
    {
        unset($this->tabs[$tabId]);
        return $this;
    }

    public function getTab(string $tabId): ?TabItem
    {
        return $this->tabs[$tabId] ?? null;
    }

    public function getTabs(): array
    {
        $tabs = $this->tabs;
        usort($tabs, fn (TabItem $a, TabItem $b) => $a->getPriority() <=> $b->getPriority());
        return $tabs;
    }

    public function hasTabs(): bool
    {
        return !empty($this->tabs);
    }

    // ─── Tab Label Container Classes ───

    public function getTabLabelContainerClass(): array
    {
        return $this->tabLabelContainerClass;
    }

    /**
     * Merge classes with defaults.
     */
    public function setTabLabelContainerClass(array $class): self
    {
        $this->tabLabelContainerClass = $this->mergeClasses($this->tabLabelContainerClass, $class);
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

    // ─── Active / Default Tab ───

    public function getActiveTab(): ?string
    {
        if ($this->activeTabId !== null) {
            return $this->activeTabId;
        }

        if ($this->defaultTabId !== null) {
            return $this->defaultTabId;
        }

        $firstTab = $this->getFirstTab();
        return $firstTab ? $firstTab->getId() : null;
    }

    public function setActiveTab(string $tabId): self
    {
        if (isset($this->tabs[$tabId])) {
            $this->activeTabId = $tabId;
        }
        return $this;
    }

    public function getDefaultTabId(): ?string
    {
        return $this->defaultTabId;
    }

    public function setDefaultTabId(string $tabId): self
    {
        if (isset($this->tabs[$tabId])) {
            $this->defaultTabId = $tabId;
        }
        return $this;
    }

    public function toArray(): array
    {
        $config = [];
        foreach ($this->tabs as $id => $tab) {
            $config[$id] = $tab->toArray();
        }
        return $config;
    }

    // ─── Helpers ───

    private function getFirstTab(): ?TabItem
    {
        $tabs = $this->getTabs();
        return !empty($tabs) ? $tabs[0] : null;
    }

    private function mergeClasses(array $default, array $additional): array
    {
        return array_values(array_unique(array_filter(array_merge($default, $additional))));
    }

    public static function create(): self
    {
        return new self();
    }
}