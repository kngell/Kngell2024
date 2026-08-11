<?php

declare(strict_types=1);

class SummerBannerMediaConfigBuilder
{
    public static function buildConfigs(): array
    {
        $configs = [];

        foreach (SummerBannerPosition::cases() as $position) {
            $configs[] = self::buildConfigForPosition($position);
        }

        return $configs;
    }

    private static function buildConfigForPosition(SummerBannerPosition $position): MediaSectionConfig
    {
        $fieldName = sprintf('block_metadata[image][%s]', $position->value);
        $altTextName = sprintf('image-alt%s', $position->value);

        return MediaSectionConfig::create(
            key: $position->value,
            title: $position->getTitle(),
            dropzoneConfig: DropzoneConfig::create('media')
                ->setFieldName($fieldName)
                ->setDragText('Drag & drop image or click to upload')
                ->setHintText('Recommended: 1920x1080 • Max 2MB')
                ->setMultiple(false),
        )
            ->setWrapperClass(['banner-image'])
            ->setShowRequired(false)
            ->setAltTextHint('Describe the image for accessibility')
            ->setSectionClass(['media-section'])
            ->setAltTextName($altTextName);
    }
}