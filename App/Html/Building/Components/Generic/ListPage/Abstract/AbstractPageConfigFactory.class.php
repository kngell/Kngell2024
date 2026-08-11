<?php

declare(strict_types=1);

abstract class AbstractPageConfigFactory
{
    use BuildSectionRendererTrait;

    public function __construct(protected IconBuilder $iconBuilder)
    {
    }

    public function createPageConfig(): PageConfig
    {
        $entity = $this->entityDescriptor();
        return PageConfig::create($entity->key)
            ->setLayoutBuilder($this->getLayoutBuilder())
            ->setDisplayKey($this->getdisplayKey())
            ->setHeaderTitle($this->headerTitle())
            ->setBeforeRenderCallback($this->getBeforeRenderCallback())
              ->setSectionGroupManager($this->sectionGroupManager())
            ->setTabConfig($this->tabConfig())
            ->setEnumClass($this->getEnumClass())
            ->setSections($this->buildSections())
            ->setAssets($this->getAssets())
            ->setExpectedControllerClass($this->getExpectedControllerClass())
            ->setEntityKey($this->getEntityKey())
            ->setHasContainer($this->hasContainer())
            ->setContainerClass($this->getContainerClass())
            ->setSectionRenderer($this->getSectionRenderer())
            ->setFormId($this->getFormId())
            ->setDefaultInputLayoutName($this->getDefaultInputLayoutName())
            ->setFieldLayouts($this->getFieldLayouts())
            ->setFieldRenderer($this->getFieldRenderer());
    }

    public function createAdminHeaderConfig(): AdminHeaderConfig
    {
        return new AdminHeaderConfig(
            title: $this->headerTitle(),
            breadcrumbs: $this->breadcrumbs(),
            primaryActions: $this->headerButtons(),
        );
    }

    public function breadcrumbs(): array
    {
        return [];
    }

    public function headerButtons(): array
    {
        return [];
    }

    // ─── Overridable defaults ────────────────────────────────

    public function headerTitle(): string
    {
        $e = $this->entityDescriptor();
        return "{$e->displayName} Manager";
    }

    public function getExpectedControllerClass(): ?string
    {
        return null;
    }

    protected function tabConfig(): ?TabConfig
    {
        return null;
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        return null;
    }

    protected function getBeforeRenderCallback(): mixed
    {
        return null;
    }

    protected function getEnumClass(): ?string
    {
        return null;
    }

    protected function getFormId(): ?string
    {
        return null;
    }

    protected function getDefaultInputLayoutName(): ?string
    {
        return null;
    }

    protected function getFieldLayouts(): array
    {
        return [];
    }

    protected function hasContainer(): bool
    {
        return false;
    }

    protected function getContainerClass(): array
    {
        return [];
    }

    protected function getdisplayKey(): ?string
    {
        return null;
    }

    protected function getLayoutBuilder(): ?PageLayoutInterface
    {
        return null;
    }

    protected function getRenderers(): array
    {
        return [
        ];
    }

    protected function getFieldHandlers(): array
    {
        return [
        ];
    }

    protected function getAssets(): array
    {
        return[];
    }

    protected function getFormSectionEnumClass(): ?string
    {
        return null;
    }

    protected function getEntityKey(): ?string
    {
        return null;
    }
    // ─── Required by subclasses ──────────────────────────────

    abstract protected function entityDescriptor(): EntityDescriptor;

    protected function buildSections(): array
    {
        return [];
    }

    protected function defaultSectionTitle(): ?string
    {
        return null;
    }

    protected function defaultSectionIcon(): ?string
    {
        return 'icon-edit';
    }

    // ─── Helper methods for building fields ──────────────────

    protected function getSectionGroups(): array
    {
        return [];
    }

    protected function useTabbedLayout(): bool
    {
        return $this->tabConfig() !== null && $this->tabConfig()->hasTabs();
    }
}
