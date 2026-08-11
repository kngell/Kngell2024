<?php

declare(strict_types=1);

class CategoryContentSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return CategorySection::CONTENT_AREA->value;
    }

    protected function getSectionConfig(array $formValues = []): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key: 'content-area',
            title: 'Content Area',
        )
            ->setWrapperClass(['content-area'])
            ->setIcon('icon-edit2')
            ->setShowRequired(false);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'content',
                'name' => 'content',
                'type' => 'textarea',
                'label' => 'Content',
                'rows' => 8,
                'footer' => [
                    'error' => '',
                ],
            ],
        ];
    }
}