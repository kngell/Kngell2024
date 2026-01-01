<?php

declare(strict_types=1);

final class InventorySection extends BaseFormSection
{
    public function __construct(
        HtmlBuilder $builder,
        private StockStatusService $stockStatusService,
    ) {
        parent::__construct($builder);
    }

    public function getKey(): string
    {
        return 'inventory';
    }

    public function getConfig(array $formValues = []): array
    {
        $stockStatuses = $this->stockStatusService->getStockStatusOptions();
        return [
            [
                'key' => 'sku',
                'name' => 'sku',
                'label' => 'SKU',
                'placeholder' => 'Enter SKU here...',
                'type' => 'text',
            ],
            [
                'key' => 'stock-quantity',
                'name' => 'stock_quantity',
                'label' => 'Stock Quantity',
                'placeholder' => 'Enter stock quantity',
                'type' => 'number',
            ],
            [
                'key' => 'stock-status',
                'name' => 'stock_status_id',
                'map' => 'stock_status.id',
                'label' => 'Stock Status',
                'type' => 'select',
                'default' => $formValues['stock_status_id'] ?? '',
                'options' => $stockStatuses,
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],

            [
                'key' => 'barcode',
                'name' => 'barcode',
                'label' => 'Barcode',
                'placeholder' => 'Enter Product barcode...',
                'type' => 'text',
            ],
            [
                'key' => 'allow_back_orders',
                'name' => 'allow-back-orders',
                'label' => 'Allow Backorders',
                'class' => 'span-all',
                'type' => 'checkbox',
            ],
            [
                'key' => 'is-track-stock',
                'name' => 'is_track_stock',
                'label' => 'Is Track Stock',
                'class' => 'span-all',
                'type' => 'checkbox',
            ],
        ];
    }
}