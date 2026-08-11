<?php

declare(strict_types=1);

class FooterAboutSectionConfigBuilder implements FormSectionConfigBuilderInterface
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
                key: AboutSectionKeys::BASICS->value,
                title: 'Basic Infos',
            )->setWrapperClass(['basic-information'])
                ->setIcon('icon-edit')
                ->setShowRequired(false)
                ->addFields($this->getBasicsFields())
                ->setRowIndicesConfig([
                    [
                        'indices' => [0],
                        'class' => ['form-row', 'horizontal']],
                    [
                        'indices' => [1, 2],
                        'class' => ['form-row', 'horizontal']],
                    [
                        'indices' => [3, 4],
                        'class' => ['form-row', 'horizontal']],
                    [
                        'indices' => [5],
                        'class' => ['form-row', 'horizontal']],
                ]),
            RegularSectionConfig::create(
                key: AboutSectionKeys::DATES_RANGE->value,
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
            FormFieldConfig::create(name: 'content', type: 'textarea')
                ->setRows(5)
                ->setLabel('Content')
                ->setPlaceholder(' ')
                ->setRequired()
                ->setFooter(['error' => 'xxx']),
            FormFieldConfig::create(name: 'logo_url', type: 'text')
                ->setLabel('Logo Url')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'logo_icon', type: 'text')
                ->setLabel('Logo Icon')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'logo_alt', type: 'text')
                ->setLabel('Logo Alt Text')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'logo_link', type: 'text')
                ->setLabel('Logo Link')
                ->setPlaceholder(' '),
            FormFieldConfig::create(name: 'is_active', type: 'toggle-switch')
                ->setLabel('Active Status')
                ->setPosition('left')
                ->setDefaultValue(true),
        ];
    }
}