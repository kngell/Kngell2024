<?php

declare(strict_types=1);

class CategoryMediaSection extends BaseMediaSection
{
    public function getKey(): string
    {
        return CategorySection::MEDIA->value;
    }

    protected function getMediaConfig(): MediaSectionConfig
    {
        return MediaSectionConfig::create(
            key: 'category-media',
            title: 'Category Image',
            dropzoneConfig: DropzoneConfig::create('category-img')
            ->setFieldName('image_url')
            ->setDragText('Drag & drop Category image or click to upload')
            ->setHintText('Recommended: 1920x1080 • Max 2MB'),
        )->setWrapperClass(['category-image'])
            ->setShowRequired(false)
            ->setIconTitle('icon-image')
            ->setAltTextName('main_alt_text');
    }
}