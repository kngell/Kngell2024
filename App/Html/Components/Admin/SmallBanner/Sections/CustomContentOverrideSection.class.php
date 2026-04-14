<?php

declare(strict_types=1);

class CustomContentOverrideSection extends BaseRegularSection
{
    protected string $layoutType = self::LAYOUT_TWO_COLUMNS;

    public function getKey(): string
    {
        return 'custom-override';
    }

    protected function getSectionConfig(): RegularSectionConfig
    {
        return RegularSectionConfig::create('Custom Content Override', 'custom-override')
            ->setWrapperClass('custom-override')
            ->setIcon('icon-edit')
            ->setShowRequired(false);
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        return [
            // Index 0
            [
                'key' => 'custom_title',
                'name' => 'custom_title',
                'type' => 'text',
                'label' => 'Custom Title',
            ],
            // Index 1
            [
                'key' => 'custom_title_span',
                'name' => 'custom_title_span',
                'type' => 'text',
                'label' => 'Title Span',
            ],
            // Index 2
            [
                'key' => 'custom_subtitle',
                'name' => 'custom_subtitle',
                'type' => 'text',
                'label' => 'Custom Subtitle',
            ],
            // Index 3
            [
                'key' => 'custom_description',
                'name' => 'custom_description',
                'type' => 'textarea',
                'label' => 'Custom Description',
            ],
            // Index 4
            [
                'key' => 'button_text',
                'name' => 'button_text',
                'type' => 'text',
                'label' => 'Button Text',
            ],
            // Index 5
            [
                'key' => 'button_link',
                'name' => 'button_link',
                'type' => 'text',
                'label' => 'Button Link',
            ],
        ];
    }

    protected function getFieldIndicesMapping(): array
    {
        return [
            'left' => [0, 1, 2],
            'right' => [3, 4, 5],
        ];
    }
}