<?php

declare(strict_types=1);

class ProductTableHeadSection extends AbstractBaseHtmlSection implements TableSectionInterface
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
                'label' => 'Products',
                'cellType' => TableCellType::START,
                'hasCheckbox' => true,
                'hasDropdown' => true,
                'hintText' => '',
                'ariaLabel' => 'Select all products',
            ],
            [
                'key' => 'sku',
                'label' => 'SKU',
                'cellType' => TableCellType::NORMAL,
            ],
            [
                'key' => 'category',
                'label' => 'Category',
                'cellType' => TableCellType::NORMAL,
            ],
            [
                'key' => 'stock',
                'label' => 'Stock',
                'cellType' => TableCellType::NORMAL,
                'hasDropdown' => true,
                'sortable' => true,
            ],
            [
                'key' => 'price',
                'label' => 'Price',
                'cellType' => TableCellType::NORMAL,
                'hasDropdown' => true,
                'sortable' => true,
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'cellType' => TableCellType::BADGE,
                'hasDropdown' => true,
                'sortable' => true,
            ],
            [
                'key' => 'added',
                'label' => 'Added',
                'cellType' => TableCellType::NORMAL,
                'hasDropdown' => true,
                'sortable' => true,
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