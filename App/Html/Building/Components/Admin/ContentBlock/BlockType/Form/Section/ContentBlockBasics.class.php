<?php

declare(strict_types=1);

class ContentBlockBasics extends BaseRegularSection
{
    private const ROW_CONFIGS = [
        BlockType::SUMMER_BANNER->value => [
            ['indices' => [0], 'class' => ['form-row', 'horizontal']],
            ['indices' => [1, 2], 'class' => ['form-row', 'horizontal']],
            ['indices' => [3], 'class' => ['form-row']],
            ['indices' => [4, 5], 'class' => ['form-row']],
            ['indices' => [6, 7], 'class' => ['form-row']],
            ['indices' => [8], 'class' => ['form-row']],
        ],
        'default' => [
            ['indices' => [0, 1], 'class' => ['form-row', 'horizontal']],
            ['indices' => [2, 3], 'class' => ['form-row', 'horizontal']],
            ['indices' => [4], 'class' => ['form-row']],
            ['indices' => [5, 6], 'class' => ['form-row']],
            ['indices' => [7, 8], 'class' => ['form-row']],
            ['indices' => [9], 'class' => ['form-row']],
        ],
    ];

    protected SectionLayout $layoutType = SectionLayout::LAYOUT_CUSTOM_ROWS;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        FormSectionHeader $header,
        private PageSectionOptionsService $optionsService,
        private BlockType $blockType,
    ) {
        parent::__construct($builder, $iconBuilder, $header);
    }

    public function getKey(): string
    {
        return BlockTypeSection::BASICS->value;
    }

    #[Override]
    protected function getFieldsConfig(array $formValues = []): array
    {
        return [];
    }

    protected function getSectionConfig(array $formValues = []): ?RegularSectionConfig
    {
        $config = RegularSectionConfig::create(
            key: BlockTypeSection::BASICS->value,
            title: 'Basic Information',
        )
            ->setWrapperClass(['basic-information'])
            ->setIcon('icon-edit')
            ->setShowRequired(true);

        // Section selector
        $config->addField(
            FormFieldConfig::create('section_id', 'select')
                ->setLabel('Select Section')
                ->setPlaceholder(' ')
                ->setRequired()
                ->withRightIcon()
                ->setOptions($this->optionsService->getActiveOptions())
                ->setFooter(['error' => ''])
                ->setDisabled(false),
        );

        // Position selector (skip for Summer Banner)
        if ($this->blockType !== BlockType::SUMMER_BANNER) {
            $config->addField(
                FormFieldConfig::create('block_metadata[position]', 'select')
                    ->setLabel('Select Banner Position')
                    ->setOptions($this->getPositionOptions())
                    ->withRightIcon()
                    ->setRequired(true)
                    ->setFooter(['error' => '']),
            );
        }

        // Title
        $config->addField(
            FormFieldConfig::create('title', 'text')
                ->setLabel('Title')
                ->setFooter(['error' => ''])
                ->setRequired(true),
        );

        // Title Span
        $config->addField(
            FormFieldConfig::create('block_metadata[title_span]', 'text')
                ->setLabel('Title Span')
                ->setPlaceholder(' ')
                ->setFooter(['hint' => 'Example: "Pro" - appears inside a span tag']),
        );

        // Subtitle
        $config->addField(
            FormFieldConfig::create('subtitle', 'textarea')
                ->setLabel('Subtitle')
                ->setRows(3)
                ->setPlaceholder('Created to change everything for the better. for everyone'),
        );

        // Page Target
        $config->addField(
            FormFieldConfig::create('page_target', 'text')
                ->setLabel('Page Target')
                ->setPlaceholder(' ')
                ->setFooter(['hint' => ' ']),
        );

        // Sort Order
        $config->addField(
            FormFieldConfig::create('sort_order', 'number')
                ->setLabel('Sort Order')
                ->setDefaultValue(0)
                ->setPlaceholder(' '),
        );

        // Button Text
        $config->addField(
            FormFieldConfig::create('button_text', 'text')
                ->setLabel('Button Text')
                ->setPlaceholder('Shop Now'),
        );

        // Button Link
        $config->addField(
            FormFieldConfig::create('button_link', 'text')
                ->setLabel('Button Link')
                ->setPlaceholder(' '),
        );

        // Active Status
        $config->addField(
            FormFieldConfig::create('is_active', 'toggle-switch')
                ->setLabel('Active Status')
                ->setPosition('left')
                ->setDefaultValue(true),
        );

        return $config;
    }

    protected function getRowIndicesConfig(): array
    {
        $blockTypeValue = $this->blockType->value;
        return self::ROW_CONFIGS[$blockTypeValue] ?? self::ROW_CONFIGS['default'];
    }

    private function getPositionOptions(): array
    {
        return match ($this->blockType) {
            BlockType::SMALL_BANNER => SmallBannerPosition::getOptions(),
            BlockType::BIG_BANNER => BigBannerPosition::getOptions(),
            default => [],
        };
    }
}