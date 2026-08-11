<?php

declare(strict_types=1);

final class CategoryTableConfigFactory extends AbstractTableConfigFactory
{
    use EntityDisplayTrait;

    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::CATEGORY->value,
            displayName: 'Category',
            plural: 'categories',
            basePath: '/admin/category-page',
        );
    }

    protected function expectedController(): string
    {
        return CategoryListController::class;
    }

    protected function captionText(): string
    {
        return 'This table lists categories with their name, images, content, '
            . 'navigation, and actions. Each category row starts with a checkbox '
            . 'followed by an image and category name.';
    }

    /** @return TableColumn[] */
    protected function columns(): array
    {
        $e = $this->entityDescriptor();
        $placeholder = $this->emptyPlaceholder();

        return [
            new TableColumn(
                key: 'select',
                cellType: TableCellType::START,
                colClass: 'table__col--start',
                label: 'Categories',
                hasCheckbox: true,
                hasDropdown: true,
                ariaLabel: 'Select all categories',
                checkboxName: $e->checkboxName(),
                thumbnail:    fn (Category $c): string => $this->show($c, 'image_url'),
                thumbnailAlt: fn (Category $c): string => $this->show($c, 'main_alt_text') ?: $placeholder,
                title: fn (Category $c): string => $this->show($c, 'name') ?: $placeholder,
                subtitle: fn (Category $c): string => $this->show($c, 'short_description') ?: $placeholder,
            ),
            new TableColumn(
                key: 'icon',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Icon',
                sortable: false,
                hasDropdown: true,
                value: fn (Category $c): string => $this->show($c, 'icon') ?: $placeholder,
            ),
            new TableColumn(
                key: 'min_price',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Min Price',
                value: fn (Category $c): ?string => $this->presenter->showField($c, 'min_price'),
            ),
            new TableColumn(
                key: 'max_price',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Max Price',
                value: fn (Category $c): ?string => $this->presenter->showField($c, 'max_price'),
            ),
            new TableColumn(
                key: 'price_range',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Price Range',
                value: fn (Category $c): ?string => $this->presenter->showField($c, 'price_range'),
            ),
            new TableColumn(
                key: 'date_added',
                cellType: TableCellType::NORMAL,
                colClass: 'table__col--normal',
                label: 'Added At',
                value: fn (Category $c): string => $this->presenter->showField($c, 'created_at'),
            ),
            new TableColumn(
                key: 'action',
                cellType: TableCellType::ACTION,
                colClass: 'table__col--action',
                label: 'Action',
                ariaLabel: 'Actions',
                idField: 'public_id',
                idValue:        fn (Category $c): string => $this->presenter->showField($c, 'public_id'),
                actionsBuilder: fn (Category $c): array => $this->rowActions($c, $c->getPublicId(), CategoryLinks::CONFIRM_DELETION->value),
            ),
        ];
    }
}