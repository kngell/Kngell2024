<?php

declare(strict_types=1);

class HeroMediaSection extends BaseFieldSection
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getKey(): string
    {
        return 'media';
    }

    public function getConfig(array $formValues = []): array
    {
        return [
            [
                'title' => 'Hero Image',
                'key' => 'hero-image',
                'name' => 'image_url',
                'type' => 'dropzone',
                'dropzoneStyle' => 'modern',
                'multiple' => false,
                'dragText' => 'Drag & drop hero image or click to upload',
                'hintText' => 'Recommended: 1920x1080 • Max 2MB',
                'icon' => 'icon-upload',
            ],
            [
                'key' => 'alt-text',
                'name' => 'image_alt',
                'type' => 'text',
                'placeholder' => ' ',
                'label' => 'Hero Alt Text',
                'counter' => '0/255',
            ],
        ];
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        $uploadBody = $form->tag('div')->class('upload-body')->add(
            ...$fields,
            // $form->tag('div')->class('upload-single')->custom(['data-state' => 'empty'])->add(
            //     // ...$fields,
            //     $form->tag('div')->class('upload-single__icon')->add(
            //         $this->iconBuilder->createIcon($form, 'icon-upload', 'Upload', ['upload']),
            //     ),
            //     $form->tag('div')->class('upload-single__text')->add(
            //         $form->tag('span')->class('upload-single__main-text')->content('Drag & drop or click to upload'),
            //         $form->tag('span')->class('upload-single__hint-text')->content('PNG, JPG, GIF • Max 5MB'),
            //     ),
            //     $form->input('file')->accept('image/*')->name($field['name])),
            // ),
        );
        return [
            $this->header->getComponent('Media', 'upload-header'),
            $uploadBody,
        ];
    }
}