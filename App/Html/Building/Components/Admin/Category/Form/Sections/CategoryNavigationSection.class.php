<?php

declare(strict_types=1);

class CategoryNavigationSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return CategorySection::NAVIGATION_INFOS->value;
    }

    protected function getSectionConfig(array $formValues = []): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key: 'navigation-infos',
            title:'Navigation and Visibility',
        )->setWrapperClass(['navigation-infos'])
            ->setIcon('icon-edit2')
            ->setShowRequired(false);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'custom-url',
                'name' => 'custom_url',
                'type' => 'text',
                'label' => 'Custom URL',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'redirect-url',
                'name' => 'redirect_url',
                'type' => 'text',
                'label' => 'Redirect URL',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'max-depth',
                'name' => 'max_depth',
                'type' => 'text',
                'label' => 'Max Depth',
                'footer' => [
                    'error' => '',
                ],
            ],
        ];
    }
}