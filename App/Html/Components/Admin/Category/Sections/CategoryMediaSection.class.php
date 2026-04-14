<?php

declare(strict_types=1);

class CategoryMediaSection extends BaseMediaSection
{
    public function getKey(): string
    {
        return 'category-media';
    }

    protected function getMediaConfig(): MediaSectionConfig
    {
        return (new MediaSectionConfig())
            ->setDropzoneKey('category-img')
            ->setDropzoneName('image_url')
            ->setDragText('Drag & drop Category image or click to upload')
            ->setHintText('Recommended: 1920x1080 • Max 2MB')
            ->setTitle('Category Image')
            ->setWrapperClass('category-image')
            ->setShowRequired(false)
            ->setIconTitle('icon-image');
    }
}