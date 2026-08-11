<?php

declare(strict_types=1);

abstract class AbstractFooterSectionConfigBuilder implements FormSectionConfigBuilderInterface
{
    // ─── Interface implementation ─────────────────────────────

    public function buildRegularConfig(): array
    {
        return [
            $this->getBasicsSection(),
            $this->getDateRangeSection(),
        ];
    }

    public function buildMediaConfig(): array
    {
        return [];
    }

    // ─── Abstract methods ─────────────────────────────────────

    abstract protected function getBasicsSection(): RegularSectionConfig;

    abstract protected function getDateRangeSection(): RegularSectionConfig;

    // ─── Helper methods ──────────────────────────────────────

    protected function createDateRangeSection(string $key, string $title): RegularSectionConfig
    {
        return RegularSectionConfig::create($key, $title)
            ->addField(
                FormFieldConfig::create('valid_from', 'date')
                    ->setLabel('Valid From')
                    ->setPlaceholder(' '),
            )
            ->addField(
                FormFieldConfig::create('valid_to', 'date')
                    ->setLabel('Valid To')
                    ->setPlaceholder(' '),
            )
            ->setRowIndicesConfig([
                [
                    'indices' => [0, 1],
                    'class' => ['form-row', 'horizontal'],
                ],
            ]);
    }

    protected function createToggleField(string $name, string $label, bool $default = true): FormFieldConfig
    {
        return FormFieldConfig::create($name, 'toggle-switch')
            ->setLabel($label)
            ->setPosition('left')
            ->setDefaultValue($default);
    }

    protected function createNumberField(string $name, string $label, int $default = 0): FormFieldConfig
    {
        return FormFieldConfig::create($name, 'number')
            ->setLabel($label)
            ->setPlaceholder('Sort order')
            ->setDefaultValue($default);
    }

    protected function createTextField(string $name, string $label, ?string $placeholder = null): FormFieldConfig
    {
        return FormFieldConfig::create($name, 'text')
            ->setLabel($label)
            ->setPlaceholder($placeholder ?? ' ')
            ->setFooter(['error' => 'xxx']);
    }
}