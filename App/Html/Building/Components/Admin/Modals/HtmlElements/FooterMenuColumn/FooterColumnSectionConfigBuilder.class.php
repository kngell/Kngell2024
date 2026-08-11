<?php

declare(strict_types=1);

final class FooterColumnSectionConfigBuilder extends AbstractFooterSectionConfigBuilder
{
    protected function getBasicsSection(): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key: FooterColumnSectionKeys::BASICS->value,
            title: 'Basic Information',
        )
        ->addField(
            $this->createTextField('column_key', 'Column Key')
                ->setId('column-key'),
        )
        ->addField(
            $this->createTextField('title', 'Column Title', 'Title')
                ->setId('column-title')
                ->setRequired(),
        )
        ->addField(
            $this->createNumberField('sort_order', 'Sort Order')
                ->setId('sort-order'),
        )
        ->addField(
            $this->createToggleField('is_active', 'Active Status')
                ->setId('is-active'),
        )
        ->setRowIndicesConfig([
            [
                'indices' => [0, 1],
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [2],
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [3],
                'class' => ['form-row'],
            ],
        ]);
    }

    protected function getDateRangeSection(): RegularSectionConfig
    {
        return $this->createDateRangeSection(
            FooterColumnSectionKeys::DATE_RANGE->value,
            'Date Range',
        );
    }
}