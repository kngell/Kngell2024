<?php

declare(strict_types=1);

abstract class AbstractFormConfigFactory
{
    use BuildSectionRendererTrait;

    public function createFormConfig(): FormConfig
    {
        $entity = $this->entityDescriptor();
        return FormConfig::create($entity->key)
            ->setHeaderTitle($this->headerTitle())
            ->setDefaultSectionTitle($this->defaultSectionTitle())
            ->setDefaultSectionIcon($this->defaultSectionIcon())
            ->setSubmitText($this->submitText())
            ->setSubmitIcon($this->submitIcon())
            ->setCustomAttributes($this->customAttributes())
            ->setFormClass($this->formClass())
            ->setFormId($this->formId())
            ->setFormName($this->formName())
            ->setFooterClass($this->footerClass())
            ->setShowProgressBar($this->showProgressBar())
            ->setFooterEnabled($this->isFooterEnabled())
            ->setDefaultInputLayoutName($this->defaultInputLayoutName())
            ->setFieldLayouts($this->getFieldLayouts())
            ->setFieldHandlers($this->getFieldHandlers())
            ->setFormContainerClass($this->getFormContainerClass())
            ->setLayoutBuilder($this->getLayoutBuilder())
            ->setSectionConfigs($this->buildSectionsConfig())
            ->setSections($this->buildSections())
            ->setFields($this->getFields())
            ->setHiddenFields($this->getHiddenFields())
            ->setSectionGroupManager($this->sectionGroupManager())
            ->setEnumClass($this->getEnumClass())
            ->setTabConfig($this->tabConfig())
            ->setTabComponentConfig($this->getTabComponentConfig())
            ->setStepConfig($this->stepConfig())
            ->setAssets($this->getAssets())
            ->setDisplayKey($this->getDisplayKey())
            ->setStandAloneFooter($this->getStandAloneFooter())
            ->setFieldRenderer($this->getFieldRenderer())
            ->setSectionRenderer($this->getSectionRenderer())
            ->showFormHeader($this->showFormHeader())
            ->setSectionParent($this->getSectionParent());
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

    protected function showFormHeader(): bool
    {
        return true;
    }

    protected function getSectionParent(): ?HtmlSectionInterface
    {
        return null;
    }

    protected function tabConfig(): ?TabConfig
    {
        return null;
    }

    protected function getTabComponentConfig(): ?TabComponentConfig
    {
        return null;
    }

    protected function stepConfig(): ?stepConfig
    {
        return null;
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        return null;
    }

    protected function getDisplayKey(): ?string
    {
        return null;
    }

    protected function getLayoutBuilder(): ?FormLayoutInterface
    {
        return null;
    }

    protected function defaultInputLayoutName(): ?string
    {
        return null;
    }

    protected function getRenderers(): array
    {
        return [
        ];
    }

    protected function getAssets(): array
    {
        return[];
    }

    protected function getFieldHandlers(): array
    {
        return [];
    }

    protected function getFooterClass(): array
    {
        return [];
    }

    protected function isShowProgressBar(): bool
    {
        return true;
    }

    protected function getFieldLayouts(): array
    {
        return [];
    }

    protected function isFooterEnabled(): bool
    {
        return true;
    }

    protected function footerClass(): array
    {
        return [];
    }

    protected function showProgressBar(): bool
    {
        return false;
    }

    protected function getStandAloneFooter(): bool
    {
        return false;
    }

    protected function getEnumClass(): ?string
    {
        return null;
    }
    // ─── Required by subclasses ──────────────────────────────

    abstract protected function entityDescriptor(): EntityDescriptor;

    /**
     * Build section instances for the form.
     *
     * @return array<RegularSectionConfig|MediaSectionConfig>
     */
    protected function buildSectionsConfig(): array
    {
        return [];
    }

    protected function idGenerator(): FieldIdGenerator
    {
        return new FieldIdGenerator();
    }

    protected function getVariationGroupRenderer(): ?VariationGroupRenderer
    {
        return null;
    }

    protected function getDropzoneRenderer(): ?DropzoneRenderer
    {
        return null;
    }

    protected function buildSections(): array
    {
        return [];
    }

    protected function getFields(): array  // Note: typo preserved
    {
        return [];
    }

    protected function getHiddenFields(): array
    {
        return [
        ];
    }

    protected function defaultSectionTitle(): ?string
    {
        return null;
    }

    protected function defaultSectionIcon(): ?string
    {
        return 'icon-edit';
    }

    protected function submitText(): string
    {
        return 'Save';
    }

    protected function submitIcon(): string
    {
        return 'icon-save';
    }

    protected function formHandlerClass(): ?string
    {
        return null;
    }

    protected function getFormContainerClass(): array
    {
        return [];
    }

    protected function validatorClass(): ?string
    {
        return null;
    }

    /** @return array<string, mixed> */
    protected function customAttributes(): array
    {
        $e = $this->entityDescriptor();
        return [
            'data-form-type' => $e->key,
            'data-entity-name' => $e->key,
            'data-validate' => 'true',
        ];
    }

    /** @return string[] */
    protected function formClass(): array
    {
        $e = $this->entityDescriptor();
        return ["{$e->key}-frm"];
    }

    protected function formId(): string
    {
        return 'small-banner-form';
    }

    protected function formName(): string
    {
        $e = $this->entityDescriptor();
        return "{$e->key}-frm";
    }

    // ─── Helper methods for building fields ──────────────────

    protected function textField(string $name, string $label, bool $required = false, ?string $placeholder = null, ?string $hint = null): FormFieldConfig
    {
        $field = FormFieldConfig::create($name, 'text')
            ->setLabel($label)
            ->setRequired($required);

        if ($placeholder) {
            $field->setPlaceholder($placeholder);
        }
        if ($hint) {
            $field->setHint($hint);
        }

        return $field;
    }

    protected function textareaField(string $name, string $label, bool $required = false, ?string $placeholder = null, ?string $hint = null, int $rows = 5): FormFieldConfig
    {
        $field = FormFieldConfig::create($name, 'textarea')
            ->setLabel($label)
            ->setRequired($required)
            ->setAttributes(['rows' => $rows]);

        if ($placeholder) {
            $field->setPlaceholder($placeholder);
        }
        if ($hint) {
            $field->setHint($hint);
        }

        return $field;
    }

    protected function selectField(string $name, string $label, array $options, bool $required = false, ?string $placeholder = null): FormFieldConfig
    {
        $field = FormFieldConfig::create($name, 'select')
            ->setLabel($label)
            ->setOptions($options)
            ->setRequired($required)
            ->setRightIcon(['icon' => 'icon-arrow-down', 'aria' => 'Dropdown arrow']);

        if ($placeholder) {
            $field->setPlaceholder($placeholder);
        }

        return $field;
    }

    protected function customSelectField(string $name, string $label, bool $required = false, bool $searchable = true, ?string $placeholder = null): FormFieldConfig
    {
        $field = FormFieldConfig::create($name, 'custom-select')
            ->setLabel($label)
            ->setRequired($required)
            ->setSearchable($searchable)
            ->setInputLayout('custom-select')
            ->setRightIcon(['icon' => 'icon-arrow-down', 'aria' => 'Dropdown arrow']);

        if ($placeholder) {
            $field->setPlaceholder($placeholder);
        }

        return $field;
    }

    protected function dropzoneField(string $name, string $label, ?string $helpText = null, bool $required = false): FormFieldConfig
    {
        $field = FormFieldConfig::create($name, 'dropzone')
            ->setLabel($label)
            ->setRequired($required);

        if ($helpText) {
            $field->setHint($helpText);
        }

        return $field;
    }

    protected function hiddenField(string $name, ?string $map = null): FormFieldConfig
    {
        $field = FormFieldConfig::create($name, 'hidden');
        if ($map) {
            $field->setMap($map);
        }
        return $field;
    }

    protected function getTabConfig(): ?TabConfig
    {
        return null;
    }

    protected function getSectionGroups(): array
    {
        return [];
    }

    protected function useTabbedLayout(): bool
    {
        return $this->getTabConfig() !== null && $this->getTabConfig()->hasTabs();
    }
}