<?php

declare(strict_types=1);

final class CategorySection extends BaseFieldSection
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
        return 'category';
    }

    public function getConfig(array $formValues = []): array
    {
        $options = $this->categoryService->getSelectOptions();
        return [
            [
                'key' => 'category',
                'name' => 'category_id',
                'map' => 'category.id',
                'label' => 'Product Category',
                'type' => 'select',
                'options' => $options,
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],
        ];
    }
}