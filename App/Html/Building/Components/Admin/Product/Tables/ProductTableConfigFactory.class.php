<?php

declare(strict_types=1);

final class ProductTableConfigFactory extends AbstractTableConfigFactory
{
    use EntityDisplayTrait;

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::PRODUCT->value,
            displayName: 'Products',
            plural: 'products',
            basePath: '/admin/admin',
        );
    }

    #[Override]
    protected function columns(): array
    {
        $e = $this->entityDescriptor();
        $placeholder = $this->emptyPlaceholder();
        return [
            new TableColumn(
                key: 'select',
                cellType: TableCellType::START,
                colClass: 'table__col--start',
                label: 'Products',
                hasCheckbox: true,
                hasDropdown: true,
                hintText: '',
                ariaLabel: 'Select all products',
                checkboxName: $e->checkboxName(),
                thumbnail: fn (ProductShow $p) => $this->show($p, 'main_image'),
                thumbnailAlt: fn (ProductShow $p) => $this->show($p, 'alt_text' ?: ''),
                title:fn (ProductShow $p) => $this->show($p, 'name' ?: $placeholder),
                subtitle: fn (ProductShow $p) => $this->formatVariationCount(
                    $p->getProductVariationShow(),
                ),
            ),
            new TableColumn(
                key: 'sku',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'SKU',
                sortable: true,
                hasDropdown: true,
                bodyCellModifierClass: 'sku',
                value: fn (ProductShow $p) => $this->presenter->show($p, $this->show($p, 'sku'), 'sku') ?: $placeholder,
            ),
            new TableColumn(
                key: 'category',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Category',
                value: fn (ProductShow $p) => $this->presenter->show(
                    $p,
                    $this->show($p, 'category.name' ?: $placeholder),
                    'category',
                ),
            ),
            new TableColumn(
                key: 'stock',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Stock Quantity',
                value: fn (ProductShow $p) => $this->presenter->show(
                    $p,
                    (string) $p->getStockQuantity(),
                    'stockQuantity',
                ) ?: $placeholder,
            ),
            new TableColumn(
                key: 'price',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Price',
                value: fn (ProductShow $p) => $this->presenter->showRelated(
                    $p,
                    'productRegionalPrice',
                    'basePrice',
                ) ?: $placeholder,
            ),
            new TableColumn(
                key: 'status',
                cellType: TableCellType::BADGE,
                colClass: 'table__col--badge',
                badgeClasses:['badge', 'badge--warning'],
                label: 'Status',
                value: fn (ProductShow $p) => $this->presenter->showRelated(
                    $p,
                    'productStatus',
                    'name',
                ) ?: $placeholder,
            ),
            new TableColumn(
                key: 'date_added',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Added At',
                value: fn (ProductShow $p) => $this->presenter->showField($p, 'created_at') ?: $placeholder,
            ),
            new TableColumn(
                key: 'action',
                cellType: TableCellType::ACTION,
                colClass: 'table__col--action',
                label: 'Action',
                ariaLabel: 'Actions',
                idField: 'public_id',
                idValue: fn (ProductShow $p) => $this->presenter->showField($p, 'public_id'),
                actionsBuilder: fn (ProductShow $p) => $this->rowActions($p, $p->getPublicId(), '/admin/product-confirm-deletion/confirm', [
                    'show' => 'product-show',
                    'edit' => 'product-edit',
                ]),
            ),
        ];
    }

    #[Override]
    protected function expectedController(): string
    {
        return AdminController::class;
    }

    #[Override]
    protected function captionText(): string
    {
        return 'This table lists products with their SKU, category, stock, price, status, date added and actions.Each product row starts with a checkbox followed by an image and product name.';
    }

    private function formatVariationCount(array $variations): string
    {
        $count = count($variations);
        return match (true) {
            $count === 0 => 'No variants',
            $count === 1 => '1 Variant',
            default => "{$count} Variants",
        };
    }
}