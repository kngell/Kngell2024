<?php

declare(strict_types=1);

final class MediaSection extends BaseFieldSection
{
    use ProductFormSectionLayoutTrait;
    private const string INPUT_FILE_CLASS = 'media-file';

    public function getKey(): string
    {
        return ProductSection::MEDIA->value;
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                'title' => 'Main image',
                'key' => 'main-image',
                'name' => 'main_image',
                'type' => 'dropzone',
                'alt' => 'Main Product Image',
                'src' => '#',
                'input-class' => self::INPUT_FILE_CLASS,
                'accept' => 'image/*',
                // 'multiple' => true,
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
                'key' => 'alt_text-main-image',
                'name' => 'alt_text',
                'type' => 'text',
                'label' => 'Image Alt Text',
                'placeholder' => 'Enter alt text for the main image',
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
            [
                'key' => 'alt_text-main-video',
                'name' => 'alt_text_video',
                'type' => 'text',
                'label' => 'Video Alt Text',
                'placeholder' => 'Enter alt text for the main video',
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
                'key' => 'gallery-title',
                'name' => 'gallery_title',
                'type' => 'text',
                'label' => 'Gallery Title',
                'placeholder' => 'Enter title for the image gallery',
            ],
        ];
    }

    #[Override]
    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        $sectionClass = 'frm-section ' . $sectionKey;
        $sectionTitle = 'Product Media';

        return $form->tag('div')
            ->class($sectionClass)
            ->add(
                $form->tag('h4')
                    ->class('frm-section__title')
                    ->content($sectionTitle),
                $form->tag('div')
                    ->class('frm-section__body')
                    ->add(
                        $form->div()->class('form-row')->add(
                            $form->div()->class('form-row', 'vertical')->add($fields[0], $fields[1]),
                            $form->div()->class('form-row', 'vertical')->add(
                                $fields[2],
                                $fields[3],
                            ),
                        ),
                        $fields[4],
                        $fields[5],
                    ),
            );
    }

    public function getFieldMapping(array $formValues = []): array
    {
        return [
            'main_image' => 'main_image',
            'product_image_gallery.*.image_url' => 'img_gallery[]',
        ];
    }
}