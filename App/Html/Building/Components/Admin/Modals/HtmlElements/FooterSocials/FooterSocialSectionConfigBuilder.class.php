<?php

declare(strict_types=1);

class FooterSocialSectionConfigBuilder implements FormSectionConfigBuilderInterface
{
    public function __construct()
    {
    }

    #[Override]
    public function buildMediaConfig(): array
    {
        return [];
    }

    #[Override]
    public function buildRegularConfig(): array
    {
        return [
            RegularSectionConfig::create(
                key: FooterSocialsSectionKeys::BASICS->value,
                title: 'Basic Information',
            )->setWrapperClass(['basic-information'])
                ->setIcon('icon-edit')
                ->setShowRequired(false)
                ->addFields($this->getBasicsFields())
                ->setRowIndicesConfig([
                    [
                        'indices' => [0, 1], // PlateForm - Name
                        'class' => ['form-row', 'horizontal']],
                    [
                        'indices' => [2, 3], // Url + Icon
                        'class' => ['form-row', 'horizontal']],
                    [
                        'indices' => [4], // Icon Class
                        'class' => ['form-row', 'horizontal']],
                    [
                        'indices' => [5], // Sort Order
                        'class' => ['form-row', 'horizontal']],
                    [
                        'indices' => [6], // Is Active
                        'class' => ['form-row', 'horizontal']],
                ]),
            RegularSectionConfig::create(
                key: FooterSocialsSectionKeys::DATE_RANGE->value,
                title: 'Dates Range',
            )->setWrapperClass(['dates-range'])
                ->setIcon('icon-edit')
                ->setShowRequired(false)
                ->addFields($this->getDateRangeFields())
                ->setRowIndicesConfig([
                    [
                        'indices' => [0, 1],
                        'class' => ['form-row', 'horizontal']],
                ]),
        ];
    }

    private function getDateRangeFields(): array
    {
        return [
            FormFieldConfig::create(name: 'valid_from', type: 'date')
                ->setLabel('Valid From')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'valid_to', type: 'date')
                ->setLabel('Valid To')
                ->setPlaceholder(' '),
        ];
    }

    private function getBasicsFields(): array
    {
        return [
            FormFieldConfig::create(
                name: 'platform',
                type: 'text',
            )
                ->setRows(5)
                ->setLabel('Platform')
                ->setPlaceholder(' ')
                ->setRequired()
                ->setFooter(['error' => 'xxx']),
            FormFieldConfig::create(name: 'name', type: 'text')
                ->setLabel('Name')
                ->setPlaceholder(' ')
                 ->setRequired()
                ->setFooter(['error' => 'xxx']),
            FormFieldConfig::create(name: 'url', type: 'text')
                ->setLabel('URL')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'icon', type: 'text')
                ->setLabel('Icon')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'icon_class', type: 'text')
                ->setLabel('Icon Class')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'sort_order', type: 'number')
                ->setLabel('Sort Order')
                ->setPlaceholder(' ')
                ->setDefaultValue(0),
            FormFieldConfig::create(name: 'is_active', type: 'toggle-switch')
                ->setLabel('Active Status')
                ->setPosition('left')
                ->setDefaultValue(true),
        ];
    }
}