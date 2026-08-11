<?php

declare(strict_types=1);

final class FooterLinkSectionConfigBuilder extends AbstractFooterSectionConfigBuilder
{
    public function __construct(
        private FooterColumnOptionService $columnService,
    ) {
    }

    protected function getBasicsSection(): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key: FooterLinkSectionKeys::BASICS->value,
            title: 'Basic Information',
        )->addField(
            $this->createTextField('title', 'Link Title', 'Title')
                ->setId('link-title')
                ->setRequired(),
        )
        ->addField(
            $this->createTextField('url', 'Link Url')
                ->setId('link-url'),
        )
        ->addField(
            FormFieldConfig::create('link_target', 'select')
                ->setLabel('Target')
                ->setPlaceholder(' ')
                ->setDefaultValue('_self')
                ->setOptions(TargetAttr::getOptions())
                ->setId('link-target')
                ->setRightIcon(
                    [
                        'icon' => 'icon-arrow-down',
                        'aria' => 'Dropdown arrow',
                    ],
                ),
        )
        ->addField(
            $this->createNumberField('sort_order', 'Sort Order')
                ->setId('link-sort-order'),
        )
        ->addField(
            $this->createToggleField('is_active', 'Active Status')
                ->setId('link-is-active'),
        )
        ->setRowIndicesConfig([
            [
                'indices' => [0],
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [1],
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [2, 3],
                'class' => ['form-row'],
            ],
            [
                'indices' => [4],
                'class' => ['form-row'],
            ],
            [
                'indices' => [5],
                'class' => ['form-row'],
            ],
        ]);
    }

    protected function getDateRangeSection(): RegularSectionConfig
    {
        return $this->createDateRangeSection(
            FooterLinkSectionKeys::DATE_RANGE->value,
            'Date Range',
        );
    }
}