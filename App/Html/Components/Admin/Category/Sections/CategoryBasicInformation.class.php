<?php

declare(strict_types=1);

class CategoryBasicInformation extends BaseRegularSection
{
    protected string $layoutType = self::LAYOUT_CUSTOM_ROWS;

    public function getKey(): string
    {
        return 'basic-information';
    }

    protected function getSectionConfig(): RegularSectionConfig
    {
        return RegularSectionConfig::create('Basic Information', 'basic-information')
            ->setWrapperClass('basic-information')
            ->setIcon('icon-edit')
            ->setShowRequired(true);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
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
                'map' => 'category.id',
                'type' => 'custom-select',
                'options' => [],
                'label' => 'Select Parent category',
                'searchable' => true,
                'rightIcon' => 'icon-arrow-down',
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
                'indices' => [2, 3],
                'class' => ['form-row', 'horizontal'],
            ],
            [
                'indices' => [4, 5, 6],
                'class' => ['form-row', 'vertical'],
            ],
        ];
    }
}