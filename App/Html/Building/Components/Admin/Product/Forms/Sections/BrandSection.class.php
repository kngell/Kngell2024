<?php

declare(strict_types=1);

final class BrandSection extends BaseFieldSection
{
    use ProductFormSectionLayoutTrait;

    public function __construct(HtmlBuilder $htmlBuilder, IconBuilder $iconBuilder, private BrandOptionsService $brandService)
    {
        return parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getKey(): string
    {
        return ProductSection::BRAND->value;
    }

    public function getConfig(array $formValues = []): array
    {
        $options = $this->brandService->getActiveOptions();
        return [
            [
                'key' => 'brand',
                'name' => 'brand_id',
                'map' => 'brand.id',
                'label' => 'Brand',
                'type' => 'select',
                'options' => $options,
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
                'defaultOption' => 1,
            ],
        ];
    }
}