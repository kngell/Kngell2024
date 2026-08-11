<?php

declare(strict_types=1);

class CategoryBasicInformation extends BaseRegularSection
{
    protected SectionLayout $layoutType = SectionLayout::LAYOUT_CUSTOM_ROWS;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        FormSectionHeader $header,
        private CategoryOptionsService $categoryService,
    ) {
        parent::__construct($builder, $iconBuilder, $header);
    }

    public function getKey(): string
    {
        return CategorySection::BASIC_INFOS->value;
    }

    protected function getSectionConfig(array $formValues = []): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key:'basic-information',
            title:'Basic Information',
        )
            ->setWrapperClass(['basic-information'])
            ->setIcon('icon-edit')
            ->setShowRequired(true);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        $this->formValues = $formValues;
        $hasValue = !empty($formValues['product_id'] ?? null);
        $options = $this->categoryService->getActiveOptions();
        return [
            // Index 0
            [
                'key' => 'public_id',
                'name' => 'public_id',
                'type' => 'hidden',
            ],
            // Index 1
            [
                'key' => 'cat_id',
                'name' => 'cat_id',
                'map' => 'id',
                'type' => 'hidden',
            ],
            // Index 2
            [
                'key' => 'name',
                'name' => 'name',
                'type' => 'text',
                'label' => 'Category Name',
                'footer' => [
                    'error' => '',
                ],
            ],
            // Index 3
            [
                'key' => 'icon',
                'name' => 'icon',
                'type' => 'text',
                'label' => 'Category Icon',
                'footer' => [
                    'error' => '',
                ],
            ],
            // Index 4
            [
                'key' => 'parent',
                'name' => 'parent_id',
                // 'map' => 'category.id',
                'type' => 'custom-select',
                'options' => $options,
                'label' => 'Select Parent category',
                'searchable' => true,
                'searchPlaceholder' => 'Category...',
                'rightIcon' => ['icon' => 'icon-arrow-down', 'aria' => 'Dropdown arrow'],
                'inputLayout' => 'custom-select',
                'has-value' => $hasValue ? 'true' : 'false',
                'footer' => [
                    'error' => 'Please select a product',
                ],
            ],
            // Index 5
            [
                'key' => 'short-description',
                'name' => 'short_description',
                'type' => 'text',
                'label' => 'Short Description',
            ],
            // Index 6
            [
                'key' => 'description',
                'name' => 'description',
                'type' => 'textarea',
                'label' => 'Description',
            ],
        ];
    }

    protected function getRowIndicesConfig(): array
    {
        return [
            [
                'indices' => [0, 1, 2, 3],
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [4, 5, 6],
                'class' => ['form-row', 'vertical'],
            ],
        ];
    }
}