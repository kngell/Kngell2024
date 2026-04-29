<?php

declare(strict_types=1);

class ClassicDropzoneHandler extends AbstractDropzoneHandler
{
    private const string TITLE_CLASS = 'input-box__media-title';
    private const array MEDIA_CLASS = ['input-box__media-upload', 'empty'];

    public function __construct(
        IconBuilder $iconBuilder,
        ?FileMetadataService $fileMetadataService = null,
    ) {
        parent::__construct($iconBuilder, $fileMetadataService);
    }

    public function getName(): string
    {
        return 'classic';
    }

    public function renderEmpty(
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
        string $fieldId,
    ): AbstractHtmlComponent {
        return $form->tag('div')
            ->class(InputBox::INPUT_BOX)
            ->add(
                $form->tag('h6')
                    ->class(self::TITLE_CLASS)
                    ->content($field['title'] ?? ''),
                $form->tag('div')
                    ->class(...self::MEDIA_CLASS)
                    ->custom(['data-media-upload' => 'true'])
                    ->add(
                        $form->tag('div')->class('media-preview empty'),
                        $form->input('file')
                            ->class($field['input-class'] ?? 'media-file')
                            ->id($fieldId)
                            ->name($field['name'])
                            ->accept($field['accept'] ?? '')
                            ->multiple($field['multiple'] ?? false),
                        $this->createMediaAvatar($field, $form),
                        $form->tag('span')->class('media-text')->content($field['dragText'] ?? ''),
                        $form->label()
                            ->for($fieldId)
                            ->class('btn', 'btn--secondary', 'btn--md-compact')
                            ->add(
                                $form->tag('span')->class('btn__label')
                                    ->content($field['buttonLabel'] ?? 'Add File'),
                            ),
                    ),
            );
    }

    public function renderPopulated(
        array $field,
        FormBuilder $form,
        array $files,
        string $fieldId,
    ): AbstractHtmlComponent {
        return $form->tag('div')
            ->class(InputBox::INPUT_BOX)
            ->add(
                $form->tag('h6')
                    ->class(self::TITLE_CLASS)
                    ->content($field['title'] ?? ''),
                $form->tag('div')
                    ->class('input-box__media-upload') // Remove 'empty' class
                    ->custom(['data-media-upload' => 'true'])
                    ->add(
                        $this->createPopulatedPreview($field, $form, $files),
                        $form->input('file')
                            ->class($field['input-class'] ?? 'media-file')
                            ->id($fieldId)
                            ->name($field['name'])
                            ->accept($field['accept'] ?? '')
                            ->multiple($field['multiple'] ?? false),
                    ),
            );
    }

    private function createMediaAvatar(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('media-avatar')
            ->add(
                $this->iconBuilder->createIcon(
                    $field['icon'] ?? 'icon-media-image',
                    $field['icon-aria'] ?? 'Media Image Avatar',
                ),
            );
    }

    private function createPopulatedPreview(array $field, FormBuilder $form, array $files): AbstractHtmlComponent
    {
        $previewContainer = $form->tag('div')
            ->class(...($field['preview-class'] ?? ['media-preview']));

        foreach ($files as $file) {
            $webPath = $file['web_path'] ?? '';
            $fileName = $file['display_name'] ?? $file['original_name'] ?? 'Unknown file';

            $previewContainer->add(
                $form->tag('div')
                    ->class('media-preview__item')
                    ->custom(['data-filename' => $fileName])
                    ->add(
                        $form->tag('div')->class('media-preview__item--img-container')->add(
                            $this->createMediaElement($form, $field, $file, $webPath),
                            $form->input('hidden')
                                ->class('existing-media-path')
                                ->name('web_path__' . rtrim($field['name'], '[]') . '[]')
                                ->value($webPath),
                        ),
                        $form->tag('div')->class('media-preview__item--icon-success')->add(
                            $this->iconBuilder->createIcon('icon-success', 'Success', ['success']),
                        ),
                        $form->button('button')->type('button')
                            ->class('media-preview__item--icon-remove')
                            ->add(
                                $form->tag('span')->class('btn__icon')->add(
                                    $this->iconBuilder->createIcon('icon-cancel', 'Remove', ['cancel']),
                                ),
                            ),
                        $form->tag('div')->class('media-preview__item--filename')->content($fileName),
                        $form->tag('div')->class('media-preview__item--filesize')
                            ->content($file['formatted_size'] ?? $this->formatFileSize($file['size'] ?? 0)),
                    ),
            );
        }

        return $previewContainer;
    }
}