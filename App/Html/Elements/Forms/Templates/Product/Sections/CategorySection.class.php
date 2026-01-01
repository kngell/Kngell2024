<?php

declare(strict_types=1);

final class CategorySection extends BaseFormSection
{
    public function __construct(
        HtmlBuilder $builder,
        private CategoryOptionsService $categoryService,
    ) {
        parent::__construct($builder);
    }

    public function getKey(): string
    {
        return 'category';
    }

    public function getConfig(array $formValues = []): array
    {
        $options = $this->categoryService->getActiveOptions();
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