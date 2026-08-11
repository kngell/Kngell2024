<?php

declare(strict_types=1);

abstract class AbstractFooterFormConfigFactory extends AbstractFormConfigFactory
{
    // ─── Footer-specific overrides ──────────────────────────────

    protected function isFooterEnabled(): bool
    {
        return false;
    }

    protected function showFormHeader(): bool
    {
        return false;
    }

    protected function getStandAloneFooter(): bool
    {
        return true;
    }

    protected function showProgressBar(): bool
    {
        return false;
    }

    protected function getHiddenFields(): array
    {
        return [
            FormFieldConfig::create(
                name: 'id',
                type: 'hidden',
            ),
        ];
    }

    protected function getFormContainerClass(): array
    {
        return array_merge(['modal-body'], $this->getFooterContainerClass());
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new ToggleSwitchHandler(),
        ];
    }

    protected function getFieldLayouts(): array
    {
        return [
            'input' => new FieldLayout(),
            'custom-select' => new CustomSelectLayout(),
        ];
    }

    protected function getLayoutBuilder(): ?FormLayoutInterface
    {
        return new SimpleFormLayout();
    }

    protected function customAttributes(): array
    {
        return array_merge(parent::customAttributes(), [
            'data-validate' => 'true',
            'data-validation-rules' => $this->getValidationRules(),
            'data-ajax-form' => $this->formId(),
        ]);
    }

    // ─── Abstract methods that subclasses must implement ──────

    abstract protected function getValidationRules(): string;

    abstract protected function getFooterContainerClass(): array;

    // ─── Footer-specific field builders ──────────────────────

    protected function toggleSwitchField(string $name, string $label, bool $default = true): FormFieldConfig
    {
        return FormFieldConfig::create($name, 'toggle-switch')
            ->setLabel($label)
            ->setPosition('left')
            ->setDefaultValue($default);
    }

    protected function dateRangeFields(): array
    {
        return [
            FormFieldConfig::create('valid_from', 'date')
                ->setLabel('Valid From')
                ->setPlaceholder(' '),
            FormFieldConfig::create('valid_to', 'date')
                ->setLabel('Valid To')
                ->setPlaceholder(' '),
        ];
    }

    protected function getDateRangeRowConfig(): array
    {
        return [
            [
                'indices' => [0, 1],
                'class' => ['form-row', 'horizontal'],
            ],
        ];
    }
}