<?php

declare(strict_types=1);

class CategoryOGMediaSection extends BaseMediaSection
{
    public function getKey(): string
    {
        return 'og-image';
    }

    protected function getMediaConfig(): MediaSectionConfig
    {
        return (new MediaSectionConfig())
            ->setDropzoneKey('og-image')
            ->setDropzoneName('og_image')
            ->setDragText('Drag & drop OG image or click to upload')
            ->setHintText('Recommended: 1200x630 • Max 2MB')
            ->setTitle('Open Graph Image')
            ->setWrapperClass('og-image')
            ->setShowRequired(false);
    }
}