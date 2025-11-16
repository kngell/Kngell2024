<?php

declare(strict_types=1);

final class MediaSection extends BaseFormSection
{
    private const string INPUT_FILE_CLASS = 'media-file';

    public function getKey(): string
    {
        return 'media';
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                // Title
                'title' => 'Main image',
                // Input Configuration
                'key' => 'main-image',
                'name' => 'main_image[]',
                'type' => 'dropzone',
                'alt' => 'Main Product Image',
                'src' => '#',
                'input-class' => self::INPUT_FILE_CLASS,
                'accept' => 'image/*',
                'multiple' => true,
                // Icons Configuration
                'icon' => 'icon-media-image',
                'icon-aria' => 'Media Image Avatar',
                // Image Preview Configuration
                'img-class' => 'image',
                'preview-class' => [
                    'media-preview',
                    'main-image',
                ],
                // Buttons Configuration
                'buttonLabel' => 'Upload Main Image',
                // Text Configuration
                'dragText' => 'Drag and drop the main image here, or click to browse',
            ],
            [
                // Title
                'title' => 'Image Gallery',
                // Input Configuration
                'key' => 'img-gallery',
                'name' => 'img_gallery[]',
                'type' => 'dropzone',
                'alt' => 'Product Image Gallery',
                'src' => '#',
                'input-class' => self::INPUT_FILE_CLASS,
                'accept' => 'image/*',
                'multiple' => true,
                // Icons Configuration
                'icon' => 'icon-media-image',
                'icon-aria' => 'Media Image Avatar',
                // Image Preview Configuration
                'preview-class' => [
                    'media-preview',
                    'image-gallery',
                ],
                'img-class' => 'image',
                // Buttons Configuration
                'buttonLabel' => 'Add Images',
                // Text Configuration
                'dragText' => 'Drag and drop the Gallery image here, or click to browse',
            ],
            [
                // Title
                'title' => 'Main Video',
                // Input Configuration
                'key' => 'main-video',
                'name' => 'main_video',
                'type' => 'dropzone',
                'alt' => 'Main Product Image',
                'src' => '#',
                'input-class' => self::INPUT_FILE_CLASS,
                'accept' => 'video/*',
                'multiple' => false,
                // Icons Configuration
                'icon' => 'icon-media-video',
                'icon-aria' => 'Media Video Avatar',
                // Image Preview Configuration
                'img-class' => 'video',
                'preview-class' => [
                    'media-preview',
                    'main-video',
                ],
                // Buttons Configuration
                'buttonLabel' => 'Upload Main Image',
                // Text Configuration
                'dragText' => 'Drag and drop the main image here, or click to browse',
            ],
        ];
    }
}