<?php

declare(strict_types=1);

final class CategorySection extends BaseFormSection
{
    public function __construct(
        HtmlBuilder $builder,
        private CategoryModel $categoryModel,
    ) {
        parent::__construct($builder);
    }

    public function getKey(): string
    {
        return 'category';
    }

    public function getConfig(array $formValues = []): array
    {
        $categories = $this->categoryModel->getActiveCategories();
        return [
            [
                'key' => 'category',
                'name' => 'category_id',
                'label' => 'Product Category',
                'type' => 'select',
                'options' => [
                    '' => 'Select a category',
                    'active' => 'Watch',
                    'clothing' => 'Clothing',
                    'books' => 'Books',
                    'furniture' => 'Furniture',
                ],
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],
            [
                'key' => 'subcategory',
                'name' => 'subcategory',
                'label' => 'Product Sub Category',
                'type' => 'select',
                'options' => [
                    '' => 'Select a subcategory',
                    'active' => 'Watch',
                    'clothing' => 'Clothing',
                    'books' => 'Books',
                    'furniture' => 'Furniture',
                ],
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],
            [
                'key' => 'product-tag',
                'name' => 'product-tag',
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
                // 'customElements' => [
                //     [
                //         'tag' => 'div',
                //         'class' => 'tag-preview',
                //         'attributes' => [
                //             'data-role' => 'tag-preview',
                //         ],
                //     ],
                // ],
            ],
        ];
    }
}