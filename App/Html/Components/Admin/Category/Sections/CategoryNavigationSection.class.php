<?php

declare(strict_types=1);

class CategoryNavigationSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return 'navigation-infos';
    }

    protected function getSectionConfig(): RegularSectionConfig
    {
        return RegularSectionConfig::create('Navigation and Visibility', 'navigation-infos')
               ->setWrapperClass('navigation-infos')
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