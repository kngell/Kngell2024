<?php

declare(strict_types=1);

abstract class RendererConfig
{
    protected FieldIdGenerator $idGenerator;
    protected ?SectionRenderer $sectionRenderer = null;
    protected ?FieldRenderer $fieldRenderer = null;
    protected array $files = [];
    protected array|Entity $formValues = [];
    protected bool $editMode = false;
    protected ?string $formId = null;
    protected ?string $defaultInputLayoutName = null;
    protected array $fieldLayouts = [];
    protected array $fieldHandlers = [];
    protected ?array $formContainerClass = [];
    protected null|FormLayoutInterface|PageLayoutInterface $layoutBuilder;
    protected ?TabConfig $tabConfig = null;
    protected ?StepConfig $stepConfig = null;
    protected ?SectionGroupManager $sectionGroupManager = null;
    protected ?TabComponentConfig $tabComponentConfig = null;
    protected array $footerClass = [];
    protected bool $showProgressBar = false;

    /** @var array<RegularSectionConfig|MediaSectionConfig> */
    protected array $sectionConfigs = [];

    protected ?HtmlSectionInterface $sectionParent = null;

    /** @var string[] */
    protected array $sections = [];

    /** @var FormFieldConfig[] */
    protected array $hiddenFields = [];

    /** @var FormFieldConfig[] */
    protected array $fields = [];

    protected ?string $enumClass = null;
    protected array $assets = [];
    protected ?string $displayKey = null;
    protected bool $standAloneFooter = false;

    /** @var DropzoneConfig[] */
    protected array $dropzones = [];

    protected array $renderers = [];
    protected bool $wrapFormSections = false;
    protected array $sectionsWrapperClass = [];
    protected bool $showFormHeader = true;

    public function __construct()
    {
        $this->idGenerator = new FieldIdGenerator();
    }

    public function withTabs(callable $tabBuilder): static
    {
        $tabConfig = new TabConfig();
        $tabBuilder($tabConfig);
        $this->tabConfig = $tabConfig;

        // Automatically use TabbedFormLayout if tabs are configured
        if ($this->tabConfig->hasTabs()) {
            $this->layoutBuilder = new TabbedFormLayout($this->sectionGroupManager);
        }

        return $this;
    }

    public function setTabConfig(?TabConfig $tabConfig): static
    {
        if ($tabConfig === null) {
            return $this;
        }
        $this->tabConfig = $tabConfig;

        if ($tabConfig->hasTabs() && $this->layoutBuilder === null) {
            $this->layoutBuilder = new TabbedFormLayout($this->sectionGroupManager);
        }

        return $this;
    }

    public function setSectionGroupManager(?SectionGroupManager $manager): static
    {
        $this->sectionGroupManager = $manager;
        return $this;
    }

    public function addSection(FormSectionConfig $section): static
    {
        $this->sections[] = $section;
        return $this;
    }

    public function addField(FormFieldConfig $field): static
    {
        $this->fields[] = $field;
        return $this;
    }

    public function getAllFields(): array
    {
        if (!empty($this->fields)) {
            return $this->fields;
        }

        return [];
    }

    public function getSectionGroupManager(): ?SectionGroupManager
    {
        return $this->sectionGroupManager;
    }

    public function addSectionGroup(SectionGroup $group): static
    {
        if ($this->sectionGroupManager === null) {
            $this->sectionGroupManager = SectionGroupManager::create();
        }
        $this->sectionGroupManager->addGroup($group);
        return $this;
    }

    public function hasSectionGroups(): bool
    {
        return $this->sectionGroupManager !== null && $this->sectionGroupManager->hasGroups();
    }

    public function getAllSectionKeys(): array
    {
        if ($this->sectionGroupManager) {
            return $this->sectionGroupManager->getAllSectionKeys();
        }
        return [];
    }

    public function getGroupForSection(string $sectionKey): ?string
    {
        if ($this->sectionGroupManager) {
            return $this->sectionGroupManager->getGroupForSection($sectionKey);
        }
        return null;
    }

    public function getWrapperClassForGroup(string $groupKey): array
    {
        if ($this->sectionGroupManager) {
            $group = $this->sectionGroupManager->getGroup($groupKey);
            if ($group) {
                return $group->getWrapperClass();
            }
        }
        return [];
    }

    public function addDropzone(DropzoneConfig $dropzone): static
    {
        $this->dropzones[$dropzone->getKey()] = $dropzone;
        return $this;
    }

    public function getDropzone(string $key): ?DropzoneConfig
    {
        return $this->dropzones[$key] ?? null;
    }

    public function addTab(TabItem $tab): static
    {
        if ($this->tabConfig === null) {
            $this->tabConfig = TabConfig::create();
        }
        $this->tabConfig->addTab($tab);

        // Update layout builder if this is the first tab
        if ($this->tabConfig->hasTabs() && !$this->layoutBuilder instanceof TabbedFormLayout) {
            $this->layoutBuilder = new TabbedFormLayout($this->sectionGroupManager);
        }

        return $this;
    }

    public function hasTabs(): bool
    {
        return $this->tabConfig !== null && $this->tabConfig->hasTabs();
    }

    public function validate(): array
    {
        $errors = [];

        if ($this->hasTabs() && $this->hasSectionGroups()) {
            foreach ($this->tabConfig->getTabs() as $tab) {
                foreach ($tab->getSectionGroups() as $groupKey) {
                    if (!$this->sectionGroupManager->getGroup($groupKey)) {
                        $errors[] = "Tab '{$tab->getId()}' references unknown section group '{$groupKey}'";
                    }
                }
            }
        }

        return $errors;
    }

    public function getFieldId(array $field): ?string
    {
        return $this->idGenerator->generateId($this->getFormId(), $field);
    }

    public function getInputLayoutNameForField(array $field): ?string
    {
        if (($field['type'] ?? '') === 'custom-select') {
            return 'custom-select';
        }
        if (($field['type'] ?? '') === 'checkbox') {
            return 'checkbox';
        }
        if (in_array($field['type'] ?? '', ['text', 'textarea', 'email', 'number', 'url', 'search', 'select', 'toggle-switch', 'checkbox', 'date', 'tel'])) {
            return 'input';
        }
        return null;
    }

    /**
     * @return FieldIdGenerator
     */
    public function getIdGenerator(): FieldIdGenerator
    {
        return $this->idGenerator;
    }

    /**
     * @param FieldIdGenerator $idGenerator
     *
     * @return static
     */
    public function setIdGenerator(FieldIdGenerator $idGenerator): static
    {
        $this->idGenerator = $idGenerator;

        return $this;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param array $files
     *
     * @return RendererConfig
     */
    public function setFiles(array $files): RendererConfig
    {
        $this->files = $files;

        return $this;
    }

    /**
     * @return array|Entity
     */
    public function getFormValues(): array|Entity
    {
        return $this->formValues;
    }

    /**
     * @param array|Entity $formValues
     *
     * @return RendererConfig
     */
    public function setFormValues(array|Entity $formValues): RendererConfig
    {
        $this->formValues = $formValues;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEditMode(): bool
    {
        return $this->editMode;
    }

    /**
     * @param bool $editMode
     *
     * @return RendererConfig
     */
    public function setEditMode(bool $editMode): RendererConfig
    {
        $this->editMode = $editMode;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormId(): ?string
    {
        return $this->formId;
    }

    /**
     * @param null|string $formId
     *
     * @return static
     */
    public function setFormId(?string $formId): static
    {
        $this->formId = $formId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDefaultInputLayoutName(): ?string
    {
        return $this->defaultInputLayoutName;
    }

    /**
     * @param null|string $defaultInputLayoutName
     *
     * @return RendererConfig
     */
    public function setDefaultInputLayoutName(?string $defaultInputLayoutName): RendererConfig
    {
        $this->defaultInputLayoutName = $defaultInputLayoutName;

        return $this;
    }

    /**
     * @return array
     */
    public function getFieldLayouts(): array
    {
        return $this->fieldLayouts;
    }

    /**
     * @param array $fieldLayouts
     *
     * @return static
     */
    public function setFieldLayouts(array $fieldLayouts): static
    {
        $this->fieldLayouts = $fieldLayouts;

        return $this;
    }

    /**
     * @return null|SectionRenderer
     */
    public function getSectionRenderer(): ?SectionRenderer
    {
        return $this->sectionRenderer;
    }

    /**
     * @param null|SectionRenderer $sectionRenderer
     *
     * @return RendererConfig
     */
    public function setSectionRenderer(?SectionRenderer $sectionRenderer): RendererConfig
    {
        $this->sectionRenderer = $sectionRenderer;

        return $this;
    }

    /**
     * @return null|FieldRenderer
     */
    public function getFieldRenderer(): ?FieldRenderer
    {
        return $this->fieldRenderer;
    }

    /**
     * @param null|FieldRenderer $fieldRenderer
     *
     * @return static
     */
    public function setFieldRenderer(?FieldRenderer $fieldRenderer): static
    {
        $this->fieldRenderer = $fieldRenderer;

        return $this;
    }

    /**
     * @return array
     */
    public function getFieldHandlers(): array
    {
        return $this->fieldHandlers;
    }

    /**
     * @param array $fieldHandlers
     *
     * @return static
     */
    public function setFieldHandlers(array $fieldHandlers): static
    {
        $this->fieldHandlers = $fieldHandlers;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getFormContainerClass(): ?array
    {
        return $this->formContainerClass;
    }

    public function hasFormContainerClass(): bool
    {
        return !empty($this->formContainerClass);
    }

    /**
     * @param null|array $formContainerClass
     *
     * @return static
     */
    public function setFormContainerClass(?array $formContainerClass): static
    {
        $this->formContainerClass = $formContainerClass;

        return $this;
    }

    /**
     * @return null|TabConfig
     */
    public function getTabConfig(): ?TabConfig
    {
        return $this->tabConfig;
    }

    /**
     * @return array
     */
    public function getFooterClass(): array
    {
        return $this->footerClass;
    }

    /**
     * @param array $footerClass
     *
     * @return static
     */
    public function setFooterClass(array $footerClass): static
    {
        $this->footerClass = $footerClass;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowProgressBar(): bool
    {
        return $this->showProgressBar;
    }

    /**
     * @param bool $showProgressBar
     *
     * @return static
     */
    public function setShowProgressBar(bool $showProgressBar): static
    {
        $this->showProgressBar = $showProgressBar;

        return $this;
    }

    /**
     * @return array
     */
    public function getSectionConfigs(): array
    {
        return $this->sectionConfigs;
    }

    /**
     * @param array $sectionConfigs
     *
     * @return static
     */
    public function setSectionConfigs(array $sectionConfigs): static
    {
        $this->sectionConfigs = $sectionConfigs;

        return $this;
    }

    /**
     * @return array
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @param array $sections
     *
     * @return static
     */
    public function setSections(array $sections): static
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     *
     * @return static
     */
    public function setFields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return array
     */
    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    /**
     * @param array $hiddenFields
     *
     * @return static
     */
    public function setHiddenFields(array $hiddenFields): static
    {
        $this->hiddenFields = $hiddenFields;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEnumClass(): ?string
    {
        return $this->enumClass;
    }

    /**
     * @param null|string $enumClass
     *
     * @return static
     */
    public function setEnumClass(?string $enumClass): static
    {
        $this->enumClass = $enumClass;

        return $this;
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * @param array $assets
     *
     * @return static
     */
    public function setAssets(array $assets): static
    {
        $this->assets = $assets;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDisplayKey(): ?string
    {
        return $this->displayKey;
    }

    /**
     * @param null|string $displayKey
     *
     * @return static
     */
    public function setDisplayKey(?string $displayKey): static
    {
        $this->displayKey = $displayKey;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStandAloneFooter(): bool
    {
        return $this->standAloneFooter;
    }

    /**
     * @param bool $standAloneFooter
     *
     * @return static
     */
    public function setStandAloneFooter(bool $standAloneFooter): static
    {
        $this->standAloneFooter = $standAloneFooter;
        return $this;
    }

    /**
     * @param array $dropzones
     *
     * @return RendererConfig
     */
    public function setDropzones(array $dropzones): RendererConfig
    {
        $this->dropzones = $dropzones;
        return $this;
    }

    /**
     * @return null|FormLayoutInterface|PageLayoutInterface
     */
    public function getLayoutBuilder(): null|FormLayoutInterface|PageLayoutInterface
    {
        return $this->layoutBuilder;
    }

    /**
     * @param null|FormLayoutInterface|PageLayoutInterface $layoutBuilder
     *
     * @return static
     */
    public function setLayoutBuilder(null|FormLayoutInterface|PageLayoutInterface $layoutBuilder): static
    {
        $this->layoutBuilder = $layoutBuilder;
        return $this;
    }

    /**
     * @return array
     */
    public function getRenderers(): array
    {
        return $this->renderers;
    }

    /**
     * @param array $renderers
     *
     * @return RendererConfig
     */
    public function setRenderers(array $renderers): RendererConfig
    {
        $this->renderers = $renderers;

        return $this;
    }

    /**
     * @return bool
     */
    public function getWrapFormSections(): bool
    {
        return $this->wrapFormSections;
    }

    /**
     * @param bool $wrapFormSections
     *
     * @return RendererConfig
     */
    public function wrapFormSections(bool $wrapFormSections): RendererConfig
    {
        $this->wrapFormSections = $wrapFormSections;

        return $this;
    }

    /**
     * @return array
     */
    public function getSectionsWrapperClass(): array
    {
        return $this->sectionsWrapperClass;
    }

    /**
     * @param array $sectionsWrapperClass
     *
     * @return RendererConfig
     */
    public function setSectionsWrapperClass(array $sectionsWrapperClass): RendererConfig
    {
        $this->sectionsWrapperClass = $sectionsWrapperClass;

        return $this;
    }

    /**
     * @return null|HtmlSectionInterface
     */
    public function getSectionParent(): ?HtmlSectionInterface
    {
        return $this->sectionParent;
    }

    /**
     * @param null|HtmlSectionInterface $sectionParent
     *
     * @return static
     */
    public function setSectionParent(?HtmlSectionInterface $sectionParent): static
    {
        $this->sectionParent = $sectionParent;

        return $this;
    }

    /**
     * @return bool
     */
    public function shouldShowFormHeader(): bool
    {
        return $this->showFormHeader;
    }

    /**
     * @param bool $showFormHeader
     *
     * @return static
     */
    public function showFormHeader(bool $showFormHeader = true): static
    {
        $this->showFormHeader = $showFormHeader;

        return $this;
    }

    public function getStepConfig(): ?StepConfig
    {
        return $this->stepConfig;
    }

    public function setStepConfig(?StepConfig $stepConfig): static
    {
        $this->stepConfig = $stepConfig;
        return $this;
    }

    /**
     * @return null|TabComponentConfig
     */
    public function getTabComponentConfig(): ?TabComponentConfig
    {
        return $this->tabComponentConfig;
    }

    /**
     * @param null|TabComponentConfig $tabComponentConfig
     *
     * @return static
     */
    public function setTabComponentConfig(?TabComponentConfig $tabComponentConfig): static
    {
        $this->tabComponentConfig = $tabComponentConfig;

        return $this;
    }
}