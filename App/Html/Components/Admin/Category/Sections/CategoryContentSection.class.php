<?php

declare(strict_types=1);

class CategoryContentSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return 'content-area';
    }

    protected function getSectionConfig(): RegularSectionConfig
    {
        return RegularSectionConfig::create('Content Area', 'content-area')
            ->setWrapperClass('content-area')
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