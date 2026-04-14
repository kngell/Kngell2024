<?php

declare(strict_types=1);

class CategoryContentStyleSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return 'content-style';
    }

    protected function getSectionConfig(): RegularSectionConfig
    {
        return RegularSectionConfig::create('Content Style', 'content-style')
               ->setWrapperClass('content-style')
               ->setIcon('icon-edit')
               ->setShowRequired(false);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        return [
            [
                'key' => 'template',
                'name' => 'template',
                'type' => 'text',
                'label' => 'Template',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'css',
                'name' => 'css_class',
                'type' => 'text',
                'label' => 'Css Class',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'bg',
                'name' => 'background_color',
                'type' => 'text',
                'label' => 'BackGround Color',
                'footer' => [
                    'error' => '',
                ],
            ],
            [
                'key' => 'color',
                'name' => 'text_color',
                'type' => 'text',
                'label' => 'Text Color',
                'footer' => [
                    'error' => '',
                ],
            ],
        ];
    }
}