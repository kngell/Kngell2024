<?php

declare(strict_types=1);
class FormMediaSection extends BaseMediaSection
{
    public function getKey(): string
    {
        return $this->config->getKey();
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;
        $imageField = $this->config->getImageField();
        $productImageField = $this->config->getProductImageField();

        // Handle product image fallback
        if (!empty($formValues)) {
            $customImage = $formValues[$imageField] ?? null;
            $productImage = $formValues[$productImageField] ?? null;

            if (empty($customImage) && !empty($productImage)) {
                $formValues[$imageField] = $productImage;
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
        if ($this->config === null) {
            throw new RuntimeException('MediaSectionConfig not set. Call setSectionConfig() first.');
        }

        return $this->config;
    }

    private function getImageHelpText(): string
    {
        $imageField = $this->config->getImageField();
        $productImageField = $this->config->getProductImageField();
        $hasCustomImage = !empty($this->formValues[$imageField]) &&
                         $this->formValues[$imageField] !== ($this->formValues[$productImageField] ?? null);

        if ($hasCustomImage) {
            return '✓ Using custom uploaded image. This overrides the product image.';
        }

        if (!empty($this->formValues[$productImageField])) {
            $productName = $this->formValues['product_name'] ?? $this->formValues['name'] ?? 'selected product';
            return sprintf('ℹ️ Currently using product image from %s. Upload a custom image to override.', $productName);
        }

        return 'No image available. Upload a custom image or select a product with an image.';
    }
}