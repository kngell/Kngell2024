<?php

declare(strict_types=1);

class BannerMediaSection extends BaseMediaSection
{
    private const string PRODUCT_IMAGE_FIELD = 'main_image';
    private const string CUSTOM_IMAGE_FIELD = 'custom_image_url';
    private const string PRODUCT_NAME_FIELD = 'product_name';

    public function getKey(): string
    {
        return 'media';
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;

        // Handle product image fallback
        if (!empty($formValues)) {
            $customImage = $formValues[self::CUSTOM_IMAGE_FIELD] ?? null;
            $productImage = $formValues[self::PRODUCT_IMAGE_FIELD] ?? null;

            if (empty($customImage) && !empty($productImage)) {
                $formValues[self::CUSTOM_IMAGE_FIELD] = $productImage;
            }
        }

        $config = parent::getConfig($formValues);

        // Add help text to the dropzone field
        if (is_array($config) && isset($config[0])) {
            $config[0]['helpText'] = $this->getImageHelpText();
        }

        return $config;
    }

    protected function getMediaConfig(): MediaSectionConfig
    {
        return (new MediaSectionConfig())
            ->setDropzoneKey('small-banner-image')
            ->setDropzoneName('custom_image_url')
            ->setDragText('Drag & drop hero image or click to upload')
            ->setHintText('Recommended: 1920x1080 • Max 2MB')
            ->setTitle('Banner Image')
            ->setWrapperClass('banner-image')
            ->setShowRequired(false)
            ->setAltTextHint('Describe the banner image for accessibility')
            ->setSectionClass('media-section');
    }

    private function getImageHelpText(): string
    {
        $hasCustomImage = !empty($this->formValues[self::CUSTOM_IMAGE_FIELD]) && $this->formValues[self::CUSTOM_IMAGE_FIELD] !== ($this->formValues[self::PRODUCT_IMAGE_FIELD] ?? null);

        if ($hasCustomImage) {
            return '✓ Using custom uploaded image. This overrides the product image.';
        }

        if (!empty($this->formValues[self::PRODUCT_IMAGE_FIELD])) {
            $productName = $this->formValues[self::PRODUCT_NAME_FIELD] ?? 'selected product';
            return sprintf('ℹ️ Currently using product image from %s. Upload a custom image to override.', $productName);
        }

        return 'No image available. Upload a custom image or select a product with an image.';
    }
}