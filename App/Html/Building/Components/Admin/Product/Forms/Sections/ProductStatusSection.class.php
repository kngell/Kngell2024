<?php

declare(strict_types=1);

final class ProductStatusSection extends BaseFieldSection
{
    use ProductFormSectionLayoutTrait;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private ProductStatusOptionsService $productStatus,
        HtmlEscaper $escaper,
    ) {
        parent::__construct($builder, $iconBuilder, $escaper);
    }

    public function getKey(): string
    {
        return ProductSection::PRODUCT_STATUS->value;
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