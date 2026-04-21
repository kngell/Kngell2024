<?php

declare(strict_types=1);

class HeroBasicInformationSection extends BaseRegularSection
{
    protected string $layoutType = self::LAYOUT_CUSTOM_ROWS;

    public function getKey(): string
    {
        return 'basic-information';
    }

    protected function getSectionConfig(): RegularSectionConfig
    {
        return RegularSectionConfig::create('Basic Information', 'basic-information')
            ->setSectionClass('form-section')
            ->setShowRequired(false);
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
                'key' => 'hero_id',
                'name' => 'hero_id',
                'map' => 'id',
                'type' => 'hidden',
            ],
            // Index 2
            [
                'key' => 'title',
                'name' => 'title',
                'type' => 'text',
                'label' => 'Hero Title',
                'footer' => [
                    'error' => '',
                ],
            ],
            // Index 3
            [
                'key' => 'specialized-title',
                'name' => 'specialized_title',
                'type' => 'text',
                'label' => 'Hero Specialized Title',
                'footer' => [
                    'error' => '',
                ],
            ],
            // Index 4
            [
                'key' => 'subtitle',
                'name' => 'subtitle',
                'type' => 'text',
                'label' => 'Hero SubTitle',
            ],
            // Index 5
            [
                'key' => 'introduction',
                'name' => 'introduction',
                'type' => 'text',
                'label' => 'Introduction Text',
            ],
            // Index 6
            [
                'key' => 'page_target',
                'name' => 'page_target',
                'type' => 'text',
                'label' => 'Page Target',
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
                'indices' => [4],
                'class' => ['form-row'],
            ],
            [
                'indices' => [5, 6],
                'class' => ['form-row', 'horizontal'],
            ],
        ];
    }
}