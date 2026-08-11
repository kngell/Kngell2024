<?php

declare(strict_types=1);

class HeroSectionConfigBuilder implements FormSectionConfigBuilderInterface
{
    public function __construct(private PageSectionOptionsService $optionsService)
    {
    }

    #[Override]
    public function buildMediaConfig(): array
    {
        return [
            MediaSectionConfig::create(
                key: HeroSectionEnum::MEDIA->value,
                title: 'Hero Image',
                dropzoneConfig: DropzoneConfig::create(
                    key: 'hero-dropzone',
                )->setMultiple(false)
                ->setFieldName('block_metadata[image][url]')
                ->setAcceptedFiles()
                ->setMaxFileSize(2),
            )->setShowRequired(false)
            ->setAltTextName('block_metadata[image][alt]'),
        ];
    }

    #[Override]
    public function buildRegularConfig(): array
    {
        return [
            RegularSectionConfig::create(
                key: HeroSectionEnum::BASIC_INFO->value,
                title: 'Basic Information',
            )->addField(
                FormFieldConfig::create(
                    name: 'section_id',
                    type: 'select',
                )->setLabel('Select a section')
                ->setPlaceholder(' ')
                ->setRequired()
                ->withRightIcon()
                ->setOptions($this->optionsService->getActiveOptions())
                ->setFooter(['error' => ''])
                ->setDisabled(false),
            )->addField(
                FormFieldConfig::create(
                    name: 'page_target',
                    type: 'text',
                )->setLabel('Page Target')
                ->setPlaceholder('/shop/iphone-14')
                ->setFooter(['hint' => 'Optional: specific page URL override']),
            )->addField(
                FormFieldConfig::create(
                    name: 'title',
                    type: 'text',
                )->setLabel('Title')
                ->setPlaceholder('Iphone 14')
                ->setFooter(['error' => '']),
            )->addField(
                FormFieldConfig::create(
                    name: 'subtitle',
                    type: 'textarea',
                )->setLabel('Subtitle')
                ->setRows(3)
                ->setPlaceholder('Created to change everything for the better. for everyone'),
            )->addField(
                FormFieldConfig::create(
                    name: 'button_text',
                    type: 'text',
                )->setLabel('Button Text')
                ->setPlaceholder('Shop Now'),
            )->addField(
                FormFieldConfig::create(
                    name: 'button_link',
                    type: 'text',
                )->setLabel('Button Link')
                ->setPlaceholder('/shop/iphone-14'),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[title_intro]',
                    type: 'text',
                )->setLabel('Title Intro')
                ->setPlaceholder(' ')
                ->setFooter(['hint' => 'Example: "Pro" - appears inside a span tag']),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[title_span]',
                    type: 'text',
                )->setLabel('Title Span')
                ->setPlaceholder(' ')
                ->setFooter(['hint' => 'Example: "Pro" - appears inside a span tag']),
            )->addField(
                FormFieldConfig::create(
                    name: 'sort_order',
                    type: 'number',
                )->setLabel('Sort Order')
                ->setDefaultValue(0),
            )->addField(
                FormFieldConfig::create(
                    name: 'is_active',
                    type: 'toggle-switch',
                )->setLabel('Active Status')
                ->setPosition('left')
                ->setDefaultValue(true),
            )->setRowIndicesConfig(
                [
                    [
                        'indices' => [0, 1],  // section_id + page target
                        'class' => ['form-row', 'horizontal'],
                    ],
                    [
                        'indices' => [2],  // title
                        'class' => ['form-row', 'horizontal'],
                    ],
                    [
                        'indices' => [3],  // subtitle
                        'class' => ['form-row', 'horizontal'],
                    ],
                    [
                        'indices' => [4, 5],  // button text + button link
                        'class' => ['form-row', 'horizontal'],
                    ],
                    [
                        'indices' => [6, 7], //title intro + title span
                        'class' => ['form-row'],
                    ],
                    [
                        'indices' => [8],
                        'class' => ['form-row'], // sort order
                    ],
                    [
                        'indices' => [9],
                        'class' => ['form-row'], // active status
                    ],
                ],
            ),

            // METADATA SECTION - Hero Specific
            RegularSectionConfig::create(
                key: HeroSectionEnum::METADATA->value,
                title: 'Hero Specific Settings',
                sectionId: 'metadata_section',
            )->setSectionBodyId('metadata_fields_container')
            ->setSectionClassBody(['metadata-fields', 'hero-metadata'])
            ->setFieldMapping($this->getFieldMapping())
            ->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[subtitle]',
                    type: 'text',
                )->setLabel('Subtitle / Intro Text')
                ->setPlaceholder('Pro.beyond.')
                ->setFooter(['hint' => 'Example: "Pro.beyond." - appears above main title']),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[animations][title]',
                    type: 'text',
                )->setLabel('Title Animation Classes')
                ->setPlaceholder('animate-fade-in-up animate-delay-200')
                ->setDefaultValue('animate-fade-in-up animate-delay-200'),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[animations][description]',
                    type: 'text',
                )->setLabel('Description Animation Classes')
                ->setPlaceholder('animate-fade-in-up animate-delay-300')
                ->setDefaultValue('animate-fade-in-up animate-delay-300')
                ->setFooter(['hint' => 'CSS classes separated by space']),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[animations][image]',
                    type: 'text',
                )->setLabel('Image Animation Classes')
                ->setPlaceholder('animate-fade-in-right animate-delay-200')
                ->setDefaultValue('animate-fade-in-right animate-delay-200'),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[image][container_class]',
                    type: 'hidden',
                )->setDefaultValue('hero__img-container'),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[image][position]',
                    type: 'hidden',
                )->setDefaultValue('main'),
            )->addField(
                FormFieldConfig::create(
                    name: 'block_metadata[description]',
                    type: 'textarea',
                )->setLabel('Description')
                ->setRows(5)
                ->setPlaceholder('Created to change everything for the better. for everyone'),
            )->setShowRequired(false)
            ->setRowIndicesConfig(
                [
                    [
                        'indices' => [0],
                        'class' => ['form-row'],
                    ],
                    [
                        'indices' => [1],
                        'class' => ['form-row'],
                    ],
                    [
                        'indices' => [2, 3, 4],
                        'class' => ['form-row', 'vertical', 'form-subsection'],
                        'title' => 'Animation Classes',
                    ],
                    [
                        'indices' => [5, 6, 7],  // alt + hidden fields
                        'class' => ['form-row'],
                    ],
                ],
            ),
        ];
    }

    private function getFieldMapping(): array
    {
        return [];
    }
}