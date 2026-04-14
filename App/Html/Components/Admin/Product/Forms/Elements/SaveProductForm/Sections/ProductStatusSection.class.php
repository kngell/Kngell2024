<?php

declare(strict_types=1);

final class ProductStatusSection extends BaseFieldSection
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private ProductStatusOptionsService $productStatus,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'product-status';
    }

    public function getConfig(array $formValues = []): array
    {
        $options = $this->productStatus->getActiveOptions();
        return [
            [
                'key' => 'product_status',
                'name' => 'status_id',
                'map' => 'product_status.id',
                'label' => 'Product Status',
                'type' => 'select',
                'options' => $options,
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],
        ];
    }
}