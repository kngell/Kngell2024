<?php

declare(strict_types=1);

class ModernDropzoneHandler extends AbstractDropzoneHandler
{
    private array $classConfig;

    public function __construct(
        IconBuilder $iconBuilder,
        ?FileMetadataService $fileMetadataService = null,
        array $classConfig = [],
    ) {
        parent::__construct($iconBuilder, $fileMetadataService);

        $this->classConfig = array_merge([
            'single-class' => 'upload-single',
            'multiple-class' => 'upload-multiple',
        ], $classConfig);
    }

    public function getName(): string
    {
        return 'modern';
    }

    public function renderEmpty(
        array $field,
        FormBuilder $form,
        AbstractForm $formInstance,
        string $fieldId,
    ): AbstractHtmlComponent {
        $multiple = $field['multiple'] ?? false;
        $baseClass = $multiple
            ? $this->classConfig['multiple-class']
            : $this->classConfig['single-class'];

        // Build the dropzone HTML structure
        $dropzone = $form->tag('div')
            ->class($baseClass)
            ->custom(['data-state' => 'empty']);

        // Icon
        $dropzone->add(
            $form->tag('div')->class($baseClass . '__icon')->add(
                $this->iconBuilder->createIcon(
                    $field['icon'] ?? 'icon-upload',
                    $field['icon-aria'] ?? 'Upload',
                ),
            ),
        );

        // Text container
        $textContainer = $form->tag('div')->class($baseClass . '__text');

        // Main text
        $textContainer->add(
            $form->tag('span')
                ->class($baseClass . '__main-text')
                ->content($field['dragText'] ?? ($multiple
                    ? 'Drag & drop files or click to upload'
                    : 'Drag & drop or click to upload')),
        );

        // Hint text
        $textContainer->add(
            $form->tag('span')
                ->class($baseClass . '__hint-text')
                ->content($field['hintText'] ?? ($multiple
                    ? 'PNG, JPG, GIF • Max 5MB each'
                    : 'PNG, JPG, GIF • Max 5MB')),
        );

        $dropzone->add($textContainer);

        // File input
        $input = $form->input('file')
            ->id($fieldId)
            ->name($field['name'])
            ->accept($field['accept'] ?? 'image/*');

        if ($multiple) {
            $input->attribute('multiple');
        }

        $dropzone->add($input);

        return $dropzone;
    }

    public function renderPopulated(
        array $field,
        FormBuilder $form,
        array $files,
        string $fieldId,
    ): AbstractHtmlComponent {
        $multiple = $field['multiple'] ?? false;
        $baseClass = $multiple
            ? $this->classConfig['multiple-class']
            : $this->classConfig['single-class'];

        // Determine if we should use the preview modifier class
        $previewClass = $baseClass . ($multiple ? ' ' . $this->classConfig['multiple-class'] . '--preview' : ' ' . $this->classConfig['single-class'] . '--preview');

        $dropzone = $form->tag('div')
            ->class($previewClass)
            ->custom([
                'data-state' => 'preview',
                'data-mode' => $multiple ? 'multiple' : 'single',
            ]);

        // Preview container with preview
        $previewContainer = $form->tag('div')->class($baseClass . '__preview-container');
        $previewDiv = $form->tag('div')->class($baseClass . '__preview');

        // Handle first file for preview (for single mode, just use first file)
        $file = reset($files);
        if ($file) {
            $isImage = $file['is_image'] ?? false;
            if ($isImage) {
                $previewDiv->add(
                    $form->tag('img')
                        ->src($file['web_path'] ?? '')
                        ->alt($field['alt'] ?? 'Preview'),
                );
            } else {
                // For non-image files, you might want to show a file icon
                $previewDiv->add($this->createFileIcon($form, $file));
            }
        }
        $previewContainer->add($previewDiv);
        $dropzone->add($previewContainer);

        // Content container
        $content = $form->tag('div')->class($baseClass . '__content');

        // Info container
        $info = $form->tag('div')->class($baseClass . '__info');
        if ($file) {
            $info->add(
                $form->tag('span')->class($baseClass . '__filename')
                    ->content($file['display_name'] ?? $file['original_name'] ?? 'File'),
                $form->tag('span')->class($baseClass . '__filesize')
                    ->content($file['formatted_size'] ?? $this->formatFileSize($file['size'] ?? 0)),
            );
        }
        $content->add($info);

        // Actions container with remove button
        $actions = $form->tag('div')->class($baseClass . '__actions');
        if ($file) {
            $actions->add(
                $form->button('button')
                    ->class('remove')  // Note: using just 'remove' as in target HTML
                    ->custom(['data-file' => $file['display_name'] ?? ''])
                    ->content('Remove'),  // Simple text as in target HTML
            );
        }
        $content->add($actions);

        $dropzone->add($content);

        // File input (hidden)
        $input = $form->input('file')
            ->id($fieldId)
            ->name($field['name'])
            ->accept($field['accept'] ?? 'image/*')
            ->attribute('hidden', '');  // Add hidden attribute

        if ($multiple) {
            $input->attribute('multiple');
        }

        $dropzone->add($input);

        // Hidden inputs for existing files
        foreach ($files as $file) {
            $dropzone->add(
                $form->input('hidden')
                    ->name('web_path__' . rtrim($field['name'], '[]') . '[]')
                    ->value($file['web_path'] ?? ''),
            );
        }

        return $dropzone;
    }
}