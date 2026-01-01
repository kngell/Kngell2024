<?php

declare(strict_types=1);

final class BrandSection extends BaseFormSection
{
    public function __construct(HtmlBuilder $formBuilder, private BrandOptionsService $brandService)
    {
        return parent::__construct($formBuilder);
    }

    public function getKey(): string
    {
        return 'brand';
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