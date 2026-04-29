<?php

declare(strict_types=1);

class ProductTableBodySection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $icon,
        private readonly HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($builder, $icon);
    }

    public function getKey(): string
    {
        return TableListSection::TBODY->value;
    }

    public function getTableSectionType(): TableListSection
    {
        return TableListSection::TBODY;
    }

    public function getConfig(array $context = []): array|AbstractHtmlComponent
    {
        $this->context = $context;

        return [
            // ──────────────────────────────────────────────────────────
            // START: Product column
            // Renderer: RowHeaderCellRenderer → <th scope="row">
            // SCSS:     .table__cell--start
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'select',
                'cellType' => TableCellType::START,
                'checkboxName' => 'products[]',
                'thumbnailAlt' => 'Product image',
                'thumbnail' => fn (ProductShow $p) => $p->getMainImage(),
                'title' => fn (ProductShow $p) => $p->getName(),
                'subtitle' => fn (ProductShow $p) => $this->formatVariationCount(
                    $p->getProductVariationShow(),
                ),
            ],

            // ──────────────────────────────────────────────────────────
            // NORMAL: SKU (with accent color modifier)
            // Renderer: NormalCellRenderer → <td>
            // SCSS:     .table__cell--normal .body-cell--sku
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'sku',
                'cellType' => TableCellType::NORMAL,
                'colorModifier' => 'sku',
                'value' => fn (ProductShow $p) => $this->presenter->show($p, $p->getSku(), 'sku'),
            ],

            // ──────────────────────────────────────────────────────────
            // NORMAL: Category
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'category',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (ProductShow $p) => $this->presenter->show(
                    $p,
                    $p->getCategory()->getName(),
                    'category',
                ),
            ],

            // ──────────────────────────────────────────────────────────
            // NORMAL: Stock
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'stock',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (ProductShow $p) => $this->presenter->show(
                    $p,
                    $p->getStockQuantity(),
                    'stockQuantity',
                ),
            ],

            // ──────────────────────────────────────────────────────────
            // NORMAL: Price
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'price',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (ProductShow $p) => $this->presenter->showRelated(
                    $p,
                    'productRegionalPrice',
                    'basePrice',
                ),
            ],

            // ──────────────────────────────────────────────────────────
            // BADGE: Status
            // Renderer: BadgeCellRenderer → <td>
            // SCSS:     .table__cell--badge
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'status',
                'cellType' => TableCellType::BADGE,
                'badgeClasses' => ['badge', 'badge--warning'],
                'value' => fn (ProductShow $p) => $this->presenter->showRelated(
                    $p,
                    'productStatus',
                    'name',
                ),
            ],

            // ──────────────────────────────────────────────────────────
            // NORMAL: Date added
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'added',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (ProductShow $p) => $this->presenter->showField($p, 'createdAt'),
            ],

            // ──────────────────────────────────────────────────────────
            // ACTION: Action buttons
            // Renderer: ActionCellRenderer → <td>
            // SCSS:     .table__cell--action
            // ──────────────────────────────────────────────────────────
            [
                'key' => 'action',
                'cellType' => TableCellType::ACTION,
                'idField' => 'public_id',
                'id' => fn (ProductShow $p) => $this->presenter->showField($p, 'public_id'),
                'actions' => $this->getActions(),
            ],
        ];
    }

    /**
     * @return ActionDefinition[]
     */
    private function getActions(): array
    {
        return [
            new ActionDefinition(
                action: '/admin/product-show',
                method: 'post',
                icon: 'icon-eye',
                iconLabel: 'Eye',
                iconClasses: ['eye'],
                buttonType: 'submit',
                screenReaderText: 'View Product',
                actionClass: 'view-action',
                csrfProtected: true,
            ),
            new ActionDefinition(
                action: '/admin/product-edit',
                method: 'get',
                icon: 'icon-edit',
                iconLabel: 'Edit',
                iconClasses: ['edit'],
                buttonType: 'submit',
                screenReaderText: 'Edit Product',
                actionClass: 'edit-action',
                csrfProtected: false,
            ),
            new ActionDefinition(
                action: '/product-confirm-deletion/confirm',
                method: 'post',
                icon: 'icon-trash',
                iconLabel: 'Delete',
                iconClasses: ['trash'],
                buttonType: 'button',
                screenReaderText: 'Delete Product',
                actionClass: 'trash-action',
                buttonCustom: ['data-action' => 'confirm-delete'],
                csrfProtected: true,
            ),
        ];
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