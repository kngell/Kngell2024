<?php

declare(strict_types=1);

class ContentBlockMediaSection extends BaseMediaSection
{
    private const string PRODUCT_IMAGE_FIELD = 'main_image';
    private const string CUSTOM_IMAGE_FIELD = 'image_url';
    private const string PRODUCT_NAME_FIELD = 'product_name';

    private const BLOCK_CONFIGS = [
        BlockType::SUMMER_BANNER->value => [
            'builder' => [SummerBannerMediaConfigBuilder::class, 'buildConfigs'],
            'is_multiple' => false,
        ],
        BlockType::BIG_BANNER->value => [
            'is_multiple' => true,
            'drag_text' => 'Drag & drop image or click to upload',
            'hint_text' => 'Recommended: 1920x1080 • Max 2MB',
        ],
        BlockType::SMALL_BANNER->value => [
            'is_multiple' => false,
            'drag_text' => 'Drag & drop image or click to upload',
            'hint_text' => 'Recommended: 1920x1080 • Max 2MB',
        ],
    ];

    private const DEFAULT_CONFIG = [
        'is_multiple' => false,
        'drag_text' => 'Drag & drop image or click to upload',
        'hint_text' => 'Recommended: 1920x1080 • Max 2MB',
    ];

    protected SectionLayout $layoutType = SectionLayout::LAYOUT_STANDARD;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        FormSectionHeader $header,
        private BlockType $blockType,
    ) {
        parent::__construct($builder, $iconBuilder, $header);
    }

    public function getKey(): string
    {
        return BlockTypeSection::MEDIA->value;
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;
        $formValues = $this->applyProductImageFallback($formValues);

        $config = parent::getConfig($formValues);

        if (is_array($config) && isset($config[0])) {
            $config[0]['helpText'] = $this->getImageHelpText();
        }

        return $config;
    }

    protected function getMediaConfig(): MediaSectionConfig|array
    {
        if ($this->blockType === BlockType::SUMMER_BANNER) {
            return $this->getSummerBlockMediaConfigs();
        }

        return $this->getStandardMediaConfig();
    }

    protected function getCustomLayout(array $media, HtmlBuilder $builder): array|AbstractHtmlComponent
    {
        $spliter = new FlexibleArraySplitter($media);
        $spliter->split(['firstRow' => 3]);
        $remaining = $spliter->getRemaining();
        $layout = [];
        $layout[] = $builder->div()->class('column')->add(
            ...$spliter->get('firstRow'),
        );
        $layout[] = $builder->div()->class('column')->add(
            ...$remaining,
        );
        return $builder->div()->class('form-row')->add(...$layout);
    }

    // protected function getFieldIndicesMapping(): array
    // {
    //     if ($this->blockType === BlockType::SUMMER_BANNER) {
    //         return [
    //             'left' => [0, 1, 2],  // Dropzone on left
    //             'right' => [3, 4], // Alt text on right
    //         ];
    //     }

    //     return parent::getFieldIndicesMapping();
    // }

    // protected function getRowIndicesConfig(): array
    // {
    //     if ($this->blockType === BlockType::SUMMER_BANNER) {
    //         return [
    //             [
    //                 'indices' => [0, 1, 3],
    //                 'class' => ['form-row'],
    //             ],
    //             [
    //                 'indices' => [4, 5],
    //                 'class' => ['form-row'],
    //             ],
    //         ];
    //     }

    //     return parent::getRowIndicesConfig();
    // }

    private function getStandardMediaConfig(): MediaSectionConfig
    {
        $config = $this->getBlockConfig();

        return MediaSectionConfig::create(
            key: 'content-block-image',
            title: 'Media Section',
            dropzoneConfig: DropzoneConfig::create('media')
                ->setFieldName('block_metadata[image][url]')
                ->setDragText($config['drag_text'])
                ->setHintText($config['hint_text'])
                ->setMultiple($config['is_multiple']),
        )
            ->setWrapperClass($this->getWrapperClass())
            ->setShowRequired(false)
            ->setAltTextHint('Describe the image for accessibility')
            ->setSectionClass(['media-section'])
            ->setAltTextName('image-alt');
    }

    private function getSummerBlockMediaConfigs(): array
    {
        if (isset(self::BLOCK_CONFIGS[$this->blockType->value]['builder'])) {
            $builder = self::BLOCK_CONFIGS[$this->blockType->value]['builder'];
            return $builder();
        }

        return $this->buildSummerBlockConfigsManually();
    }

    private function buildSummerBlockConfigsManually(): array
    {
        $configs = [];
        $positions = SummerBannerPosition::cases();

        foreach ($positions as $position) {
            $fieldName = sprintf('block_metadata[image][%s]', $position->value);
            $altTextName = sprintf('image-alt%s', $position->value);

            $configs[] = MediaSectionConfig::create(
                key: $position->value,
                title: $position->getTitle(),
                dropzoneConfig: DropzoneConfig::create('media')
                    ->setFieldName($fieldName)
                    ->setDragText('Drag & drop image or click to upload')
                    ->setHintText('Recommended: 1920x1080 • Max 2MB')
                    ->setMultiple(false),
            )
                ->setWrapperClass($this->getWrapperClass())
                ->setShowRequired(false)
                ->setAltTextHint('Describe the image for accessibility')
                ->setSectionClass(['media-section'])
                ->setAltTextName($altTextName);
        }

        return $configs;
    }

    private function applyProductImageFallback(array $formValues): array
    {
        $customImage = $formValues[self::CUSTOM_IMAGE_FIELD] ?? null;
        $productImage = $formValues[self::PRODUCT_IMAGE_FIELD] ?? null;

        if (empty($customImage) && !empty($productImage)) {
            $formValues[self::CUSTOM_IMAGE_FIELD] = $productImage;
        }

        return $formValues;
    }

    private function getBlockConfig(): array
    {
        $config = self::BLOCK_CONFIGS[$this->blockType->value] ?? self::DEFAULT_CONFIG;
        return array_merge(self::DEFAULT_CONFIG, $config);
    }

    private function getImageHelpText(): string
    {
        $hasCustomImage = !empty($this->formValues[self::CUSTOM_IMAGE_FIELD])
            && $this->formValues[self::CUSTOM_IMAGE_FIELD] !== ($this->formValues[self::PRODUCT_IMAGE_FIELD] ?? null);

        if ($hasCustomImage) {
            return '✓ Using custom uploaded image. This overrides the product image.';
        }

        if (!empty($this->formValues[self::PRODUCT_IMAGE_FIELD])) {
            $productName = $this->formValues[self::PRODUCT_NAME_FIELD] ?? 'selected product';
            return sprintf('ℹ️ Currently using product image from %s. Upload a custom image to override.', $productName);
        }

        return 'No image available. Upload a custom image or select a product with an image.';
    }

    private function getWrapperClass(): array
    {
        return match ($this->blockType) {
            BlockType::BIG_BANNER => ['big-banner-image'],
            default => ['banner-image'],
        };
    }
}