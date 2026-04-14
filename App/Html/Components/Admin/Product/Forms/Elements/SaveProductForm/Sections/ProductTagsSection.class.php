<?php

declare(strict_types=1);

final class ProductTagsSection extends BaseFieldSection
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private CategoryService $categoryService,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'product_tag';
    }

    public function getConfig(array $formValues = []): array
    {
        $options = $this->categoryService->getActiveOptions();
        return [
            [
                'key' => 'product-tags',
                'name' => 'product_tags[]',
                'label' => 'ProductTags',
                'type' => 'select',
                'options' => [
                    '' => 'Select product tag',
                    'active' => 'Watch',
                    'clothing' => 'Gadget',
                    'books' => 'Books',
                    'furniture' => 'Furniture',
                ],
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
                'customComponent' => 'tagPreview',
            ],
        ];
    }
}
