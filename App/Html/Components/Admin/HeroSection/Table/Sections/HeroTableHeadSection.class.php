<?php

declare(strict_types=1);

class HeroTableHeadSection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    public function getKey(): string
    {
        return TableListSection::THEAD->value;
    }

    public function getTableSectionType(): TableListSection
    {
        return TableListSection::THEAD;
    }

    public function getConfig(array $entities = []): array|AbstractHtmlComponent
    {
        return [
            [
                'key' => 'select',
                'label' => 'Heroes',
                'cellType' => TableCellType::START,
                'hasCheckbox' => true,
                'hasDropdown' => true,
                'hintText' => '',
                'ariaLabel' => 'Select all heroes',
            ],
            [
                'key' => 'title',
                'label' => 'Title',
                'cellType' => TableCellType::NORMAL,
                'hasDropdown' => true,
                'sortable' => true,
            ],
            [
                'key' => 'specialized-title',
                'label' => 'Span Title',
                'cellType' => TableCellType::NORMAL,
            ],
            [
                'key' => 'introduction',
                'label' => 'Introduction',
                'cellType' => TableCellType::NORMAL,
            ],
            [
                'key' => 'cta_text',
                'label' => 'Button Text',
                'cellType' => TableCellType::NORMAL,
            ],
            [
                'key' => 'cta_link',
                'label' => 'Button Link',
                'cellType' => TableCellType::NORMAL,
            ],
            [
                'key' => 'action',
                'label' => 'Action',
                'cellType' => TableCellType::ACTION,
                'ariaLabel' => 'Actions',
            ],
        ];
    }
}