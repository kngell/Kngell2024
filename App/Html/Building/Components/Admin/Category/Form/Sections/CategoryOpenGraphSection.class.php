<?php

declare(strict_types=1);

class CategoryOpenGraphSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return CategorySection::OPEN_GRAPH->value;
    }

    protected function getSectionConfig(array $formValues = []): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key: 'open-graph',
            title: 'Open Graph',
        )
            ->setWrapperClass(['open-graph'])
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