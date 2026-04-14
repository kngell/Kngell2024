<?php

declare(strict_types=1);

class CategoryOpenGraphSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return 'open-graph';
    }

    protected function getSectionConfig(): RegularSectionConfig
    {
        return RegularSectionConfig::create('Open Graph', 'open-graph')
            ->setWrapperClass('open-graph')
            ->setIcon('icon-lock')
            ->setShowRequired(false);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'og-title',
                'name' => 'og_title',
                'type' => 'text',
                'label' => 'OG Title',
            ],
            [
                'key' => 'og-description',
                'name' => 'og_deescription',
                'type' => 'textarea',
                'label' => 'OG Description',
            ],
        ];
    }
}