<?php

declare(strict_types=1);

class CategoryOGMediaSection extends BaseMediaSection
{
    public function getKey(): string
    {
        return CategorySection::OG_MEDIA->value;
    }

    protected function getMediaConfig(): MediaSectionConfig
    {
        return MediaSectionConfig::create(
            key: 'og-image',
            title:'Open Graph Image',
            dropzoneConfig: DropzoneConfig::create('og-image')
            ->setFieldName('og_image')
            ->setDragText('Drag & drop OG image or click to upload')
            ->setHintText('Recommended: 1200x630 • Max 2MB'),
        )->setWrapperClass(['og-image'])
            ->setShowRequired(false)
            ->setAltTextName('og_alt_text');
    }
}