<?php

declare(strict_types=1);

final class MediaSection extends BaseFormSection
{
    protected const array ARRAY_FIELDS = [
        'product_image_gallery.*.image_url' => 'main_image[]',
        'product_image_gallery.*.image_url' => 'img_gallery[]',
        'product_image_gallery.*' => 'product_image_gallery[]',
    ];
    private const string INPUT_FILE_CLASS = 'media-file';

    public function getKey(): string
    {
        return 'media';
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                'title' => 'Main image',
                'key' => 'main-image',
                'name' => 'main_image[]',
                'type' => 'dropzone',
                'alt' => 'Main Product Image',
                'src' => '#',
                'input-class' => self::INPUT_FILE_CLASS,
                'accept' => 'image/*',
                'multiple' => true,
                'icon' => 'icon-media-image',
                'icon-aria' => 'Media Image Avatar',
                'img-class' => 'image',
                'preview-class' => [
                    'media-preview',
                    'main-image',
                ],
                'buttonLabel' => 'Upload Main Image',
                'dragText' => 'Drag and drop the main image here, or click to browse',
            ],
            [
                'title' => 'Image Gallery',
                'key' => 'img-gallery',
                'name' => 'img_gallery[]',
                'type' => 'dropzone',
                'alt' => 'Product Image Gallery',
                'src' => '#',
                'input-class' => self::INPUT_FILE_CLASS,
                'accept' => 'image/*',
                'multiple' => true,
                'icon' => 'icon-media-image',
                'icon-aria' => 'Media Image Avatar',
                'preview-class' => [
                    'media-preview',
                    'image-gallery',
                ],
                'img-class' => 'image',
                'buttonLabel' => 'Add Images',
                'dragText' => 'Drag and drop the Gallery image here, or click to browse',
            ],
            [
                'title' => 'Main Video',
                'key' => 'main-video',
                'name' => 'main_video',
                'type' => 'dropzone',
                'alt' => 'Main Product Image',
                'src' => '#',
                'input-class' => self::INPUT_FILE_CLASS,
                'accept' => 'video/*',
                'multiple' => false,
                'icon' => 'icon-media-video',
                'icon-aria' => 'Media Video Avatar',
                'img-class' => 'video',
                'preview-class' => [
                    'media-preview',
                    'main-video',
                ],
                'buttonLabel' => 'Upload Main Image',
                'dragText' => 'Drag and drop the main image here, or click to browse',
            ],
        ];
    }

    public function getFieldMapping(array $formValues = []): array
    {
        return [
            'main_image' => 'main_image[]',
            'product_image_gallery.*.image_url' => 'img_gallery[]',
        ];
    }
}