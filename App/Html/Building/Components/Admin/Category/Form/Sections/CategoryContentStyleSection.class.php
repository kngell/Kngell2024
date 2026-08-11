<?php

declare(strict_types=1);

class CategoryContentStyleSection extends BaseRegularSection
{
    public function getKey(): string
    {
        return CategorySection::CONTENT_STYLE->value;
    }

    protected function getSectionConfig(array $formValues = []): RegularSectionConfig
    {
        return RegularSectionConfig::create(
            key:'content-style',
            title: 'Content Style',
        )
               ->setWrapperClass(['content-style'])
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